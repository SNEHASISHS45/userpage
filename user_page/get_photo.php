<?php
session_start();
include 'db_connect.php'; // Ensure db_connect.php is correctly set up

$host = 'localhost';
$db = 'user_page';
$user = 'your_username';  // Update with your actual username
$pass = 'your_password';  // Update with your actual password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$photo_id = intval($_GET['id']);

$query = "SELECT id, image_path FROM photos WHERE user_id = :user_id AND id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':id', $photo_id);
$stmt->execute();
$photo = $stmt->fetch(PDO::FETCH_ASSOC);

$photos = [];
if ($photo) {
    // Fetch all photos for navigation
    $query_all = "SELECT id, image_path FROM photos WHERE user_id = :user_id";
    $stmt_all = $pdo->prepare($query_all);
    $stmt_all->bindParam(':user_id', $user_id);
    $stmt_all->execute();
    $photos = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode([
    'photo' => $photo,
    'photos' => $photos
]);

$pdo = null; // Close PDO connection
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
