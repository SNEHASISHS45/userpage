<?php
var_dump(getenv('DB_HOST'));
var_dump(getenv('DB_PORT'));
var_dump(getenv('DB_NAME'));
var_dump(getenv('DB_USER'));
var_dump(getenv('DB_PASSWORD'));


$host = 'dpg-cvde525svqrc73efsaeg-a.oregon-postgres.render.com';
$port = '5432';
$dbname = 'sdrive';
$user = 'sdrive_user';
$password = 'VmuIcCKW8MUIUrYz8GFxIdLzTMKlbuRh'; // Ensure correct password

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
echo "Connected successfully to PostgreSQL!";

?>
