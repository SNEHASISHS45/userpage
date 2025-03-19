<?php
// Load .env variables
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception(".env file not found!");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        [$name, $value] = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

try {
    loadEnv(__DIR__ . '/.env');
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT');
    $dbname = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASS');

    // Create a connection using PostgreSQL
    $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

    if (!$conn) {
        die("Connection failed: " . pg_last_error());
    }
    echo "Connected successfully to PostgreSQL!";
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
