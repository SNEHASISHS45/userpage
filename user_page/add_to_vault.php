<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$title = $_POST['title'];
$description = $_POST['description'];
$file_path = ''; // Optional, only if you want to upload files

// Handle file upload if provided
if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = 'vault_files/';
    $file_name = basename($_FILES['file']['name']);
    $upload_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
        $file_path = $upload_path;
    } else {
        echo 'File upload failed.';
        exit();
    }
}

$query = "INSERT INTO personal_vault (user_id, title, description, file_path) VALUES (:user_id, :title, :description, :file_path)";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':title', $title);
$stmt->bindParam(':description', $description);
$stmt->bindParam(':file_path', $file_path);
$stmt->execute();

header("Location: personal_vault.php");
exit();
?>
