<?php
$host = 'localhost';
$db = 'user_page';
$user = 'your_actual_username'; // Replace with your actual username
$pass = 'your_actual_password'; // Replace with your actual password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
