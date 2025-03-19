<?php
// Load Composer autoload
require 'vendor/autoload.php';

// Load environment variables using Dotenv
use Dotenv\Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    die("Error: Failed to load .env file. " . $e->getMessage());
}

// Fetch environment variables
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

// Check if environment variables are set
if (!$host || !$port || !$dbname || !$user || !$password) {
    die("Error: Missing environment variables for database connection.");
}

// Establish PostgreSQL connection
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// Handle connection errors
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

echo "Connected successfully to PostgreSQL!";
?>
