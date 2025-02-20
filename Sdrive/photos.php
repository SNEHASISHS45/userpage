<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}

$userId = $_SESSION['user_id'];  // Get logged-in user's ID

// Handle File Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["photos"])) {
    $uploadDir = __DIR__ . "/uploads/$userId/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES["photos"]["tmp_name"] as $key => $tmpName) {
        $fileName = basename($_FILES["photos"]["name"][$key]);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $filePath)) {
            $stmt = $conn->prepare("INSERT INTO photos (filename, user_id) VALUES (?, ?)");
            $stmt->bind_param("si", $fileName, $userId);
            $stmt->execute();
        }
    }
    header("Location: index.php");
    exit;
}

// Handle Photo Deletion
if (isset($_POST['delete_id'])) {
    $photoId = $_POST['delete_id'];

    $stmt = $conn->prepare("SELECT filename FROM photos WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $photoId, $userId);
    $stmt->execute();
    $stmt->bind_result($fileName);
    $stmt->fetch();
    $stmt->close();

    if ($fileName) {
        unlink("uploads/$userId/" . $fileName);
        $stmt = $conn->prepare("DELETE FROM photos WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $photoId, $userId);
        $stmt->execute();
    }

    echo "Deleted successfully!";
    exit;
}

// Handle Image Title Update
if (isset($_POST['update_title'])) {
    $photoId = $_POST['update_id'];
    $newTitle = $_POST['new_title'];

    $stmt = $conn->prepare("UPDATE photos SET title = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $newTitle, $photoId, $userId);
    $stmt->execute();

    echo "Title updated successfully!";
    exit;
}

// Fetch Images from Database (Only for the logged-in user)
$stmt = $conn->prepare("SELECT * FROM photos WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery</title>
    <link href="https://fonts.googleapis.com/css2?family=Reem+Kufi&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Rounded" rel="stylesheet">
    <link rel="stylesheet" href="photos.css">

 
</head>
<body>


    <section class="gallery">
        <div class="row">
            <h2>Photo Gallery</h2>
        </div>
        
        <div class="upload-form">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="file" name="photos[]" multiple required>
                <button type="submit">Upload</button>
            </form>
        </div>

        <div class="row">
            <?php 
            $colCount = 0;
            $imagesPerCol = ceil($result->num_rows / 4);
            echo '<div class="col">';
            
            while ($row = $result->fetch_assoc()): 
                if ($colCount && $colCount % $imagesPerCol === 0) {
                    echo '</div><div class="col">';
                }
            ?>
                <div class="fluid-container">
                    <div class="item">
                        <div class="img">
                            <img src="uploads/<?= htmlspecialchars($row['filename']) ?>" alt="Gallery Image">
                        </div>
                        <div class="info">
                            <span class="title"><?= htmlspecialchars($row['title'] ?? $row['filename']) ?></span>
                            <button class="edit-title" data-id="<?= $row['id'] ?>">Edit Title</button>
                            <button class="delete" data-id="<?= $row['id'] ?>">Delete</button>
                        </div>
                    </div>
                </div>
            <?php 
                $colCount++;
            endwhile; 
            echo '</div>';
            ?>
        </div>
    </section>

    <div class="overlay">
        <div class="viewer">
            <div>
                <div class="alt">Image Preview</div>
                <button class="close">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <div>
                <img src="" alt="Preview">
            </div>
        </div>
    </div>

    <script>
        let items = document.querySelectorAll(".item"),
            viewer = document.querySelector(".viewer img");

        document.querySelector(".viewer .close").onclick = () => {
            document.querySelector("body").classList.toggle("overlayed");
        };

        items.forEach((item) => {
            item.onclick = () => {
                let content = item.querySelector(".img img");
                viewer.setAttribute("src", content.getAttribute("src"));
                document.querySelector(".viewer .alt").innerHTML = item.querySelector(
                    ".title"
                ).innerHTML;
                document.querySelector("body").classList.toggle("overlayed");
            };
        });

        ["load", "scroll"].forEach((eventName) => {
            window.addEventListener(eventName, (event) => {
                document.querySelectorAll(".fluid-container").forEach((item) => {
                    if (isScrolledIntoView(item)) {
                        item.classList.add("inScreen");
                    } else {
                        item.classList.remove("inScreen");
                    }
                });
            });
        });

        function isScrolledIntoView(el) {
            let rect = el.getBoundingClientRect();
            let elemTop = rect.top;
            let elemBottom = rect.bottom;
            let isVisible = elemTop >= -300 && elemBottom <= screen.height + 300;
            return isVisible;
        }

        // Edit title functionality
        let editTitleButtons = document.querySelectorAll(".edit-title");
        editTitleButtons.forEach((button) => {
            button.onclick = () => {
                let id = button.getAttribute("data-id");
                let title = prompt("Enter new title:");
                if (title) {
                    fetch(`photos.php?update_title=true&update_id=${id}&new_title=${title}`)
                        .then((response) => response.text())
                        .then((message) => console.log(message));
                    button.parentNode.querySelector(".title").innerHTML = title;
                }
            };
        });

        // Delete functionality
        let deleteButtons = document.querySelectorAll(".delete");
        deleteButtons.forEach((button) => {
            button.onclick = () => {
                let id = button.getAttribute("data-id");
                if (confirm("Are you sure you want to delete this image?")) {
                    fetch(`photos.php?delete_id=${id}`)
                        .then((response) => response.text())
                        .then((message) => console.log(message));
                    button.parentNode.parentNode.remove();
                }
            };
        });
    </script>

    <?php
   


    if (isset($_GET['update_title'])) {
        $photoId = $_GET['update_id'];
        $newTitle = $_GET['new_title'];

        $stmt = $conn->prepare("UPDATE photos SET title = ? WHERE id = ?");
        $stmt->bind_param("si", $newTitle, $photoId);
        $stmt->execute();

        echo "Title updated successfully!";
        exit;
    }

    if (isset($_GET['delete_id'])) {
        $photoId = $_GET['delete_id'];
        $stmt = $conn->prepare("SELECT filename FROM photos WHERE id = ?");
        $stmt->bind_param("i", $photoId);
        $stmt->execute();
        $stmt->bind_result($fileName);
        $stmt->fetch();
        $stmt->close();

        if ($fileName) {
            unlink("uploads/" . $fileName);
            $stmt = $conn->prepare("DELETE FROM photos WHERE id = ?");
            $stmt->bind_param("i", $photoId);
            $stmt->execute();
        }

        echo "Deleted successfully!";
        exit;
    }
    ?>

</body>
</html>

<?php $conn->close(); ?>
