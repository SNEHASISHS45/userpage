<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Fetch user's profile picture
$query = "SELECT profile_picture FROM users WHERE id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$profile_picture = $stmt->fetchColumn();
$stmt->closeCursor();
$pdo = null;

$profile_pics_dir = 'profile_pics/';
$profile_picture_path = $profile_pics_dir . $profile_picture;

// Return the profile picture path
echo htmlspecialchars($profile_picture_path);
?>
