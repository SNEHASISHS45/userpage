<?php
session_start();
include 'db_connect.php'; // Ensure this file is correctly configured
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection variables
$host = 'localhost';
$dbname = 'user_page';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Could not connect to the database.");
}

// Update activity when loading the gallery
updateActivity($pdo, $_SESSION['user_id'], 'gallery');

// Update user activity
function updateActivity($pdo, $user_id, $activity_type) {
    $now = date('Y-m-d H:i:s');
    $query = "INSERT INTO user_activity (user_id, activity_type, last_opened)
              VALUES (:user_id, :activity_type, :last_opened)
              ON DUPLICATE KEY UPDATE last_opened = VALUES(last_opened)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':activity_type', $activity_type, PDO::PARAM_STR);
    $stmt->bindParam(':last_opened', $now, PDO::PARAM_STR);
    $stmt->execute();
}

function updateStorageUsage($pdo, $user_id, $sizeChange) {
    $query = "INSERT INTO storage (user_id, storage_used) 
              VALUES (:user_id, :sizeChange) 
              ON DUPLICATE KEY UPDATE storage_used = storage_used + :sizeChange";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':sizeChange', $sizeChange, PDO::PARAM_INT);
    $stmt->execute();
}

// Fetch current storage usage
function getCurrentStorageUsage($pdo, $user_id) {
    $query = "SELECT storage_used FROM storage WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn() ?: 0; // Return 0 if no record found
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$max_storage = 1 * 1024 * 1024 * 1024; // 1GB in bytes

// Get current storage usage
$current_storage_used = getCurrentStorageUsage($pdo, $user_id);

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
            $file_size = $_FILES["photo"]["size"][$i];

            // Check if image file is an actual image
            $check = getimagesize($_FILES["photo"]["tmp_name"][$i]);
            if ($check === false) {
                $uploadOk = 0;
                $error_message .= "File " . htmlspecialchars($original_file_name) . " is not an image.<br>";
                continue; // Skip this file
            }

            // Check file size (5MB limit)
            if ($file_size > 5000000) {
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

            // Check if the user will exceed their storage limit
            if (($current_storage_used + $file_size) > $max_storage) {
                $uploadOk = 0;
                $error_message .= "Uploading " . htmlspecialchars($original_file_name) . " will exceed your storage limit.<br>";
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

                    // Update storage usage for the uploaded photo
                    updateStorageUsage($pdo, $user_id, $file_size);
                    $current_storage_used += $file_size; // Update current storage used
                } else {
                    $error_message .= "Sorry, there was an error uploading your file " . htmlspecialchars($original_file_name) . ".<br>";
                }
            }
        }

        // Update activity after file upload
        updateActivity($pdo, $user_id, 'gallery');

        // Redirect to the same page to avoid re-upload on refresh
        header("Location: gallery.php");
        exit();
    } else {
        $error_message = "No files were uploaded.";
    }
}

// Handle photo deletion
function deletePhoto($pdo, $user_id, $photo_id) {
    // Fetch photo paths using PDO
    $query = "SELECT image_path, thumbnail_path FROM photos WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $photo_id]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($photo) {
        // Get storage used before deletion
        $storage_used = filesize($photo['image_path']);
        
        // Delete photo from database
        $stmt = $pdo->prepare("DELETE FROM photos WHERE id = :id");
        $stmt->execute([':id' => $photo_id]);

        // Delete photo files
        if (file_exists($photo['image_path'])) {
            unlink($photo['image_path']);
            updateStorageUsage($pdo, $user_id, -$storage_used); // Update storage usage
        }
        if (file_exists($photo['thumbnail_path'])) {
            unlink($photo['thumbnail_path']);
        }

        // Return the updated storage usage
        return getCurrentStorageUsage($pdo, $user_id);
    }
    return false;
}

// Handle photo deletion
if (isset($_GET['delete_photo_id'])) {
    $photo_id = intval($_GET['delete_photo_id']);
    deletePhoto($pdo, $user_id, $photo_id);
}

// Handle multiple photo deletions
if (isset($_GET['photo_ids'])) {
    $photo_ids = explode(',', $_GET['photo_ids']);
    foreach ($photo_ids as $photo_id) {
        deletePhoto($pdo, $user_id, intval($photo_id));
    }
}

// Function to create a thumbnail from an image
function createThumbnail($source, $destination, $width, $height) {
    $img = null;
    $imageFileType = strtolower(pathinfo($source, PATHINFO_EXTENSION));

    switch ($imageFileType) {
        case 'jpg':
        case 'jpeg':
            $img = imagecreatefromjpeg($source);
            break;
        case 'png':
            $img = imagecreatefrompng($source);
            break;
        case 'gif':
            $img = imagecreatefromgif($source);
            break;
    }

    if ($img) {
        $thumb = imagecreatetruecolor($width, $height);
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $width, $height, imagesx($img), imagesy($img));
        imagejpeg($thumb, $destination, 90);
        imagedestroy($img);
        imagedestroy($thumb);
    } else {
        error_log("Failed to create thumbnail for $source");
    }
}

// Fetch all photos for the user
$query = "SELECT * FROM photos WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);


// After deletion logic, return the success status and the new storage usage
if (isset($_POST['delete_photo_ids'])) {
    $photo_ids = explode(',', $_POST['delete_photo_ids']);
    foreach ($photo_ids as $photo_id) {
        deletePhoto($pdo, $user_id, intval($photo_id));
    }
    echo json_encode(['success' => true, 'newStorageUsed' => getCurrentStorageUsage($pdo, $user_id), 'deletedPhotoIds' => $photo_ids]);
    exit();
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
</head>
<body>
<div class="sp">
    <!-- Spline 3D Viewer -->
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.28/build/spline-viewer.js"></script>
<spline-viewer loading-anim-type="spinner-small-light" url="https://prod.spline.design/8TpOImH7QKlXoUTY/scene.splinecode"></spline-viewer>
    </div>
<header>
    <nav>
        <h1>Dashboard</h1>
        <div class="nav-container">
            <ul id="nav-menu" class="nav-menu">
                <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="dashboard.php"><i class="fa-solid fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                <li><a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <div class="profile-info">
                <img src="<?php echo $profile_picture_path; ?>" alt="Profile Picture" class="profile-pic">
                <span class="username"><?php echo $username; ?></span>
            </div>
        </div>
    </nav>
</header>
<p>Current storage usage: <?= round($current_storage_used / (1024 * 1024), 2) ?> MB / <?= round($max_storage / (1024 * 1024), 2) ?> MB</p>
<p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
   
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
                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'gallery.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Update storage status in the DOM
                            document.getElementById('currentStorage').textContent = (response.newStorageUsed / (1024 * 1024)).toFixed(2);

                            // Remove the deleted photos from the gallery without reloading the page
                            response.deletedPhotoIds.forEach(photoId => {
                                let photoElement = document.querySelector(`.photo-checkbox[data-photo-id='${photoId}']`).closest('.photo-item');
                                if (photoElement) {
                                    photoElement.remove(); // Remove the photo from the gallery grid
                                }
                            });
                        }
                    }
                };
                xhr.send('delete_photo_ids=' + selectedIds.join(','));
            }
        } else {
            alert('No photos selected.');
        }
    });

    
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
