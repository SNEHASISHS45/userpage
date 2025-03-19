<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? null;
$port = $_ENV['DB_PORT'] ?? null;
$dbname = $_ENV['DB_NAME'] ?? null;
$user = $_ENV['DB_USER'] ?? null;
$password = $_ENV['DB_PASSWORD'] ?? null;

if (!$host || !$port || !$dbname || !$user || !$password) {
    die("Error: Missing environment variables for database connection.");
}

// Connect to PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

echo "Connected successfully!";
?>
