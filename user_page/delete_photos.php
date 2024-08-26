<?php
session_start();
include 'db_connect.php'; // Ensure db_connect.php is correctly set up

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['photo_ids'])) {
    $user_id = $_SESSION['user_id'];
    $photo_ids = $_POST['photo_ids'];

    foreach ($photo_ids as $photo_id) {
        $photo_id = intval($photo_id);

        // Fetch photo paths
        $query = "SELECT image_path, thumbnail_path FROM photos WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ii", $photo_id, $user_id);
        $stmt->execute();
        $photo = $stmt->get_result()->fetch_assoc();

        if ($photo) {
            // Delete photo from database
            $query = "DELETE FROM photos WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ii", $photo_id, $user_id);
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
