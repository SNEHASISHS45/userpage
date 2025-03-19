<?php
// Debug environment variables (optional)
var_dump([
    'DB_HOST' => getenv('DB_HOST'),
    'DB_PORT' => getenv('DB_PORT'),
    'DB_NAME' => getenv('DB_NAME'),
    'DB_USER' => getenv('DB_USER'),
    'DB_PASSWORD' => getenv('DB_PASSWORD')
]);

// Set database connection parameters (use environment variables or hardcoded values)
$host = getenv('DB_HOST') ?: 'dpg-cvde525svqrc73efsaeg-a.oregon-postgres.render.com';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'sdrive';
$user = getenv('DB_USER') ?: 'sdrive_user';
$password = getenv('DB_PASSWORD') ?: 'VmuIcCKW8MUIUrYz8GFxIdLzTMKlbuRh';

// Check if all variables are set
if (!$host || !$port || !$dbname || !$user || !$password) {
    die("Error: One or more environment variables are missing. Please check your configuration.");
}

// Attempt to connect
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// Connection error handling
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

echo "Connected successfully to PostgreSQL!";
?>
