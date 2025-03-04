<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


include 'config.php';
include 'cloudinary_config.php';

use Cloudinary\Api\Upload\UploadApi;

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}

$userId = $_SESSION['user_id'];

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'application/pdf', 'text/plain'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["photos"])) {
    foreach ($_FILES["photos"]["tmp_name"] as $key => $tmpName) {
        $fileName = basename($_FILES["photos"]["name"][$key]);
        $fileType = mime_content_type($tmpName);

        if (!in_array($fileType, $allowedTypes)) {
            echo "Invalid file type: $fileType";
            continue;
        }

        try {
            // Upload to Cloudinary
            $upload = (new UploadApi())->upload($tmpName, [
                'folder' => "sdrive_backup/$userId",
                'public_id' => pathinfo($fileName, PATHINFO_FILENAME),
            ]);
            $cloudinaryUrl = $upload['secure_url'];

            // Check for duplicate entries
            $checkStmt = $conn->prepare("SELECT id FROM photos WHERE filename = ? AND user_id = ?");
            $checkStmt->bind_param("si", $fileName, $userId);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows === 0) {
                $stmt = $conn->prepare("INSERT INTO photos (filename, user_id, filepath) VALUES (?, ?, ?)");
                $stmt->bind_param("sis", $fileName, $userId, $cloudinaryUrl);
                $stmt->execute();
                $stmt->close();
            }
            $checkStmt->close();
        } catch (Exception $e) {
            echo "Upload error: " . $e->getMessage();
        }
    }

    header("Location: index.php");
    exit();
}

// Handle Image Deletion
if (isset($_POST['delete_id'])) {
    $photoId = $_POST['delete_id'];

    $stmt = $conn->prepare("SELECT filepath FROM photos WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $photoId, $userId);
    $stmt->execute();
    $stmt->bind_result($filePath);
    $stmt->fetch();
    $stmt->close();

    if ($filePath) {
        // Extract Cloudinary Public ID
        $publicId = basename(parse_url($filePath, PHP_URL_PATH));
        $publicId = str_replace(['sdrive_backup/', '.jpg', '.png', '.webp', '.gif', '.mp4', '.pdf'], '', $publicId);

        // Delete from Cloudinary
        (new UploadApi())->destroy("sdrive_backup/$userId/$publicId");

        $stmt = $conn->prepare("DELETE FROM photos WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $photoId, $userId);
        $stmt->execute();
        $stmt->close();
    }

    
    exit();
}

// Handle Title Update
if (isset($_POST['update_title'])) {
    $photoId = $_POST['update_id'];
    $newTitle = htmlspecialchars($_POST['new_title']);

    $stmt = $conn->prepare("UPDATE photos SET title = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $newTitle, $photoId, $userId);
    $stmt->execute();
    $stmt->close();

    
    exit();
}

// Fetch Photos
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
    <link rel="stylesheet" href="css/photos/photos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />


 
</head>
<body>


    <section class="gallery">
        
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
                        <img src="<?= htmlspecialchars($row['filepath'] . '?w=300&h=300&c=fill&f_auto&q_auto') ?>" alt="Gallery Image">
                        </div>
                        <div class="info">
                            <span class="title"><?= htmlspecialchars($row['title'] ?? $row['filename']) ?></span>
                            <button class="edit-title" data-id="<?= $row['id'] ?>" style="background-color: #0d0d0d;"><i class="fa-solid fa-pen-to-square"></i></button>
                            <button class="delete" data-id="<?= $row['id'] ?>" style="background-color: #0d0d0d;"><i class="fa-solid fa-trash"></i></button>
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
                    <span class="material-symbols-rounded close"><i class="fa-solid fa-xmark"></i></span>
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
