<?php
include 'db_connect.php'; // Ensure db_connect.php is correctly set up

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if ids were provided
if (isset($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = $_POST['ids'];

    // Prepare SQL to prevent SQL injection
    $ids_placeholder = implode(',', array_fill(0, count($ids), '?'));
    $query = "SELECT image_path, thumbnail_path FROM photos WHERE id IN ($ids_placeholder) AND user_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(str_repeat('i', count($ids)) . 'i', ...array_merge($ids, [$user_id]));
    $stmt->execute();
    $photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Delete photos
    $query = "DELETE FROM photos WHERE id IN ($ids_placeholder) AND user_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param(str_repeat('i', count($ids)) . 'i', ...array_merge($ids, [$user_id]));
    $stmt->execute();
    $stmt->close();

    // Delete files from server
    foreach ($photos as $photo) {
        if (file_exists($photo['image_path'])) {
            unlink($photo['image_path']);
        }
        if (file_exists($photo['thumbnail_path'])) {
            unlink($photo['thumbnail_path']);
        }
    }
}
?>
