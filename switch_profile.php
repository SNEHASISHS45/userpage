<?php
session_start();
require 'config.php';
require '.env';

if (isset($_GET['id'])) {
    $newUserId = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT id, username, profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $newUserId);
    $stmt->execute();
    $stmt->bind_result($id, $username, $profile_pic);
    $stmt->fetch();

    if ($id) {
        $_SESSION["user_id"] = $id;
        $_SESSION["username"] = $username;
        $_SESSION["profile_pic"] = $profile_pic;
    }
    $stmt->close();

    header("Location: index.php");
    exit();
}
?>
