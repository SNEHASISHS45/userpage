<?php
session_start();

// Clear session data
$_SESSION = array();

// If a session cookie exists, delete it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Optionally, log logout activity to a file or database here
// Example: file_put_contents('logout_log.txt', "User logged out at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Redirect with feedback
header("Location: login.php?message=logged_out");
exit();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
