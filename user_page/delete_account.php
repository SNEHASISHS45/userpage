<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle account deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account'])) {
    // Prepare the statement to delete the user
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Log the user out and redirect to the login page
        session_unset();
        session_destroy();
        header("Location: login.php?message=Account deleted successfully.");
        exit();
    } else {
        echo "Error deleting account: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
