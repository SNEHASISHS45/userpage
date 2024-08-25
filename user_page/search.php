<?php
include 'db_connect.php';

$query = $_GET['query'];

$sql = "SELECT * FROM posts WHERE caption LIKE '%$query%' OR username LIKE '%$query%'";
$result = $conn->query($sql);

$search_results = array();
while ($row = $result->fetch_assoc()) {
    $search_results[] = $row;
}

echo json_encode($search_results);
?>
