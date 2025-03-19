<?php
$host = 'dpg-cvde525svqrc73efsaeg-a.oregon-postgres.render.com';
$port = '5432';
$dbname = 'sdrive';
$user = 'sdrive_user';
$password = 'VmuIcCKW8MUIUrYz8GFxIdLzTMKlbuRh';

// Connect to PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
echo "Connected successfully to PostgreSQL!";
?>
