<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Validate environment variables
$requiredVars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
foreach ($requiredVars as $var) {
    if (empty($_ENV[$var])) {
        die("Error: Missing environment variable: $var");
    }
}

// Database connection
function dbConnect() {
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASSWORD'];

    $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
    if (!$conn) {
        die("Error: Failed to connect to the database. " . pg_last_error());
    }
    return $conn;
}
?>
