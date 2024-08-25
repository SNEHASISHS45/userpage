<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['bio'], $_POST['user_id'])) {
    $bio = $_POST['bio'];
    $user_id = $_POST['user_id'];

    // Prepare SQL statement to update bio
    $query = "UPDATE users SET bio = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $bio, $user_id);

    if ($stmt->execute()) {
        // Redirect back to profile page with success message
        header("Location: profile.php?status=success");
        exit();
    } else {
        // Redirect back to profile page with error message
        header("Location: profile.php?status=error");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
