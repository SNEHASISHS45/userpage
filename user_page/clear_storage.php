<?php
require_once('storage_functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page = $_POST['page'];
    $username = $_SESSION['username'];

    clearUserStorage($username, $page);
    
    header('Location: dashboard.php');
}

function clearUserStorage($username, $page) {
    global $conn;

    $column = $page . '_storage';
    $query = "UPDATE user_storage SET $column = 0, total_storage = total_storage - $column WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
}
?>
