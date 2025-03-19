<?php
$host = getenv('dpg-cvde525svqrc73efsaeg-a.oregon-postgres.render.com');
$port = getenv('5432');
$dbname = getenv('sdrive');
$user = getenv('sdrive_user');
$password = getenv('VmuIcCKW8MUIUrYz8GFxIdLzTMKlbuRh');

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
echo "Connected successfully to PostgreSQL!";
?>
