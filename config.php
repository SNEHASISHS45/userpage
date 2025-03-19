<?php
// Load environment variables using getenv
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

// Check if all variables are set
if (!$host || !$port || !$dbname || !$user || !$password) {
    die("Error: Missing environment variables for database connection.");
}

// Connect to PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

echo "Connected successfully to PostgreSQL!";
?>
