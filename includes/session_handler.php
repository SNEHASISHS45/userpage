<?php
ob_start();
session_start();

require dirname(__DIR__) . 'config.php'; // Use dirname(__DIR__) to get parent directory path

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check Remember Me Before Redirect
if (!isset($_SESSION["user_id"]) && isset($_COOKIE["remember_me"]) && isset($_COOKIE["user_id"])) {
    $token = $_COOKIE["remember_me"];
    $user_id = $_COOKIE["user_id"];

    $stmt = $conn->prepare("SELECT id, username, profile_picture, remember_token FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $profile_picture, $stored_hashed_token);
        $stmt->fetch();

        if (password_verify($token, $stored_hashed_token)) {
            // Set session variables
            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $username;
            $_SESSION["profile_picture"] = $profile_picture;
            session_regenerate_id(true); // Prevent session fixation
        } else {
            // Invalid token - clear cookies
            setcookie("remember_me", "", time() - 3600, "/", "", false, true);
            setcookie("user_id", "", time() - 3600, "/", "", false, true);
        }
    }
    $stmt->close();
}

// Get user data
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id='$user_id'";
    $result = mysqli_query($conn, $sql);
    $user_data = mysqli_fetch_assoc($result);
}

ob_end_flush();
?>