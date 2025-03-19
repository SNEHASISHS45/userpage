<?php
$conn = mysqli_connect("localhost", "root", "", "yourdrive");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

