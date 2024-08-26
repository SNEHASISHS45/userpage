<?php
$servername = "localhost";
$username = "your_username";  // Replace with your actual username
$password = "your_password";  // Replace with your actual password
$dbname = "user_page";        // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>
