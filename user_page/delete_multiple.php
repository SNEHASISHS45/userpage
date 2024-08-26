<?php
session_start();
include 'db_connect.php'; // Ensure db_connect.php is correctly set up

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['photo_ids'])) {
    $photo_ids = explode(',', $_GET['photo_ids']);
    $photo_ids = array_map('intval', $photo_ids);

    foreach ($photo_ids as $photo_id) {
        // Fetch photo paths
        $query = "SELECT image_path, thumbnail_path FROM photos WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $photo_id);
        $stmt->execute();
        $photo = $stmt->get_result()->fetch_assoc();

        if ($photo) {
            // Delete photo from database
            $query = "DELETE FROM photos WHERE id = ?";
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("i", $photo_id);
            $stmt->execute();
            $stmt->close();

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

header("Location: gallery.php");
exit();
?>
