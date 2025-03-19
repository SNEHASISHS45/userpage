<?php

// Database configuration
$host = 'dpg-cvde525svqrc73efsaeg-a.oregon-postgres.render.com';      // Your database host
$port = '5432';           // PostgreSQL default port
$dbname = 'sdrive';     // Your database name
$user = 'sdrive_user';       // Your database username
$password = 'VmuIcCKW8MUIUrYz8GFxIdLzTMKlbuRh';   // Your database password

// Establish PostgreSQL connection
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// Handle connection errors
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

echo "Connected successfully to PostgreSQL!";
?>
