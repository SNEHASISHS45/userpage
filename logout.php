<?php
session_start();

// Forcefully destroy session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"] ?? '/',
        $params["domain"] ?? '',
        $params["secure"] ?? false,
        $params["httponly"] ?? true
    );
}
session_destroy();

// Clear all other cookies
foreach ($_COOKIE as $key => $value) {
    setcookie($key, '', time() - 3600, "/");
}

// Debug session and cookies
var_dump($_SESSION);
var_dump($_COOKIE);

// Redirect to login page
header("Location: login.php");
exit;
?>
