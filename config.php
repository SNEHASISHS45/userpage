<?php
$host = getenv('DB_HOST'); // DB_HOST should be set to 'dpg-cvde525svqrc73efsaeg-a.oregon-postgres.render.com'
$port = getenv('DB_PORT'); // DB_PORT should be set to '5432'
$dbname = getenv('DB_NAME'); // DB_NAME should be set to 'sdrive'
$user = getenv('DB_USER'); // DB_USER should be set to 'sdrive_user'
$password = getenv('DB_PASSWORD'); // DB_PASSWORD should be set to 'VmuIcCKW8MUIUrYz8GFxIdLzTMKlbuRh'

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
echo "Connected successfully to PostgreSQL!";
?>
