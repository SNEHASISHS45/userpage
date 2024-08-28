<?php
session_start();
include 'db_connect.php'; // Ensure this file is correctly configured

// Database connection setup with PDO
$host = 'localhost';
$db = 'user_page';
$user = 'your_username'; // Update with your actual username
$pass = 'your_password'; // Update with your actual password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';

// Handle photo upload
if (isset($_POST['upload_photo'])) {
    if (isset($_FILES['photo']['name']) && !empty(array_filter($_FILES['photo']['name']))) {
        $file_count = count($_FILES['photo']['name']);
        $target_dir = "uploads/";
        $thumbnail_dir = "uploads/thumbnails/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (!is_dir($thumbnail_dir)) {
            mkdir($thumbnail_dir, 0777, true);
        }

        for ($i = 0; $i < $file_count; $i++) {
            $original_file_name = basename($_FILES["photo"]["name"][$i]);
            $unique_file_name = uniqid() . '-' . $original_file_name;
            $target_file = $target_dir . $unique_file_name;
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is an actual image
            $check = getimagesize($_FILES["photo"]["tmp_name"][$i]);
            if ($check === false) {
                $uploadOk = 0;
                $error_message .= "File " . htmlspecialchars($original_file_name) . " is not an image.<br>";
                continue; // Skip this file
            }

            // Check file size (5MB limit)
            if ($_FILES["photo"]["size"][$i] > 5000000) {
                $uploadOk = 0;
                $error_message .= "File " . htmlspecialchars($original_file_name) . " is too large.<br>";
                continue; // Skip this file
            }

            // Allow only specific file formats
            if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
                $uploadOk = 0;
                $error_message .= "Only JPG, JPEG, PNG & GIF files are allowed for " . htmlspecialchars($original_file_name) . ".<br>";
                continue; // Skip this file
            }

            // Check if the file already exists in the database
            $query = "SELECT COUNT(*) FROM photos WHERE user_id = :user_id AND image_path = :image_path";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':image_path', $target_file);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                $uploadOk = 0;
                $error_message .= "File " . htmlspecialchars($original_file_name) . " already exists.<br>";
                continue; // Skip this file
            }

            // If everything is ok, try to upload the file
            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["photo"]["tmp_name"][$i], $target_file)) {
                    // Create a thumbnail
                    $thumbnail_path = $thumbnail_dir . $unique_file_name;
                    createThumbnail($target_file, $thumbnail_path, 300, 300);

                    // Insert photo information into the database using PDO
                    $query = "INSERT INTO photos (user_id, image_path, thumbnail_path) VALUES (:user_id, :image_path, :thumbnail_path)";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':image_path', $target_file);
                    $stmt->bindParam(':thumbnail_path', $thumbnail_path);
                    $stmt->execute();
                } else {
                    $error_message .= "Sorry, there was an error uploading your file " . htmlspecialchars($original_file_name) . ".<br>";
                }
            }
        }

        // Redirect to the same page to avoid re-upload on refresh
        header("Location: gallery.php");
        exit();
    } else {
        $error_message = "No files were uploaded.";
    }
}

// Handle photo deletion
if (isset($_GET['delete_photo_id'])) {
    $photo_id = intval($_GET['delete_photo_id']);

    // Fetch photo paths using PDO
    $query = "SELECT image_path, thumbnail_path FROM photos WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $photo_id);
    $stmt->execute();
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($photo) {
        // Delete photo from database
        $query = "DELETE FROM photos WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $photo_id);
        $stmt->execute();

        // Delete photo files
        if (file_exists($photo['image_path'])) {
            unlink($photo['image_path']);
        }
        if (file_exists($photo['thumbnail_path'])) {
            unlink($photo['thumbnail_path']);
        }
    }
}

// Handle multiple photo deletions
if (isset($_GET['photo_ids'])) {
    $photo_ids = explode(',', $_GET['photo_ids']);

    foreach ($photo_ids as $photo_id) {
        $photo_id = intval($photo_id);

        // Fetch photo paths using PDO
        $query = "SELECT image_path, thumbnail_path FROM photos WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $photo_id);
        $stmt->execute();
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($photo) {
            // Delete photo from database
            $query = "DELETE FROM photos WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $photo_id);
            $stmt->execute();

            // Delete photo files
            if (file_exists($photo['image_path'])) {
                unlink($photo['image_path']);
            }
            if (file_exists($photo['thumbnail_path'])) {
                unlink($photo['thumbnail_path']);
            }
        }
    }
}

// Fetch user's photos from the database using PDO
$query = "SELECT id, image_path, thumbnail_path FROM photos WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdo = null; // Close PDO connection

// Function to create a high-quality thumbnail
function createThumbnail($source, $destination, $width, $height) {
    list($source_width, $source_height, $image_type) = getimagesize($source);
    
    $image_p = imagecreatetruecolor($width, $height);
    $image = null;
    
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($source);
            break;
        default:
            return false; // Unsupported image type
    }

    if ($image !== null) {
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $source_width, $source_height);
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                imagejpeg($image_p, $destination, 100); // Quality parameter
                break;
            case IMAGETYPE_PNG:
                imagepng($image_p, $destination, 0); // Quality parameter
                break;
            case IMAGETYPE_GIF:
                imagegif($image_p, $destination);
                break;
        }
        imagedestroy($image);
        imagedestroy($image_p);
        return true;
    }
    return false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Gallery</title>
    <link rel="stylesheet" href="g.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="nav-container">
                <a href="home.php">Back to Home</a>
            </div>
        </nav>
    </header>

    <section class="gallery">
        <h1>Your Gallery</h1>

        <!-- Upload Photo Form -->
        <div class="upload-form">
            <h2>Upload Photo</h2>
            <form action="gallery.php" method="post" enctype="multipart/form-data">
                <input type="file" id="photoInput" name="photo[]" accept="image/*" multiple required>
                <button type="button" id="choosePhotoButton">Choose Photos</button>
                <button type="submit" name="upload_photo" id="submitButton">Upload Photos</button>
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Select All & Delete Multiple Form -->
        <div class="select-all-container">
            <input type="checkbox" id="selectAllCheckbox">
            <label for="selectAllCheckbox">Select All</label>
            <button class="delete-multiple-button" id="deleteMultipleButton">Delete Selected</button>
        </div>

        <!-- Photo Gallery Grid -->
        <div class="photo-gallery">
            <?php foreach ($photos as $photo): ?>
                <div class="photo-item">
                    <img src="<?php echo htmlspecialchars($photo['thumbnail_path']); ?>" alt="Photo" onclick="openModal(<?php echo $photo['id']; ?>)">
                    <input type="checkbox" class="photo-checkbox" data-photo-id="<?php echo $photo['id']; ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- The Modal -->
    <div id="photoModal" class="modal">
        <span class="prev" onclick="changeSlide(-1)">&#10094;</span>
        <span class="next" onclick="changeSlide(1)">&#10095;</span>
        <img class="modal-content" id="modalImage">
        <span class="close" onclick="closeModal()">&times;</span>
    </div>

    <script>
        let currentSlideIndex = 0;
        let slides = [];
        const modal = document.getElementById('photoModal');
        const modalImage = document.getElementById('modalImage');

        document.getElementById('choosePhotoButton').addEventListener('click', function() {
            document.getElementById('photoInput').click();
        });

        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.photo-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        document.getElementById('deleteMultipleButton').addEventListener('click', function() {
            let selectedIds = [];
            document.querySelectorAll('.photo-checkbox:checked').forEach(checkbox => {
                selectedIds.push(checkbox.getAttribute('data-photo-id'));
            });

            if (selectedIds.length > 0) {
                if (confirm('Are you sure you want to delete the selected photos?')) {
                    window.location.href = 'gallery.php?photo_ids=' + selectedIds.join(',');
                }
            } else {
                alert('No photos selected.');
            }
        });

        function openModal(photoId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_photo.php?id=' + photoId, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    slides = response.photos;
                    currentSlideIndex = slides.findIndex(photo => photo.id === photoId);
                    showSlide(currentSlideIndex);
                    modal.style.display = "block";
                }
            };
            xhr.send();
        }

        function closeModal() {
            modal.style.display = "none";
        }

        function showSlide(index) {
            if (slides.length > 0) {
                modalImage.src = slides[index].image_path;
                currentSlideIndex = index;
            }
        }

        function changeSlide(n) {
            let newIndex = (currentSlideIndex + n + slides.length) % slides.length;
            showSlide(newIndex);
        }

        window.onclick = function(event) {
            if (event.target === modal) {
                closeModal();
            }
        };
    </script>
</body>
</html>
