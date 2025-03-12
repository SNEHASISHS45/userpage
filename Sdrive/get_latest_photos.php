<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

header('Content-Type: application/json');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => "You must be logged in to view photos"]));
}

$userId = $_SESSION['user_id'];

// Get the timestamp of the last request (if provided)
$lastTimestamp = isset($_GET['last_timestamp']) ? $_GET['last_timestamp'] : date('Y-m-d H:i:s', strtotime('-5 minutes'));

// Get photos uploaded after the last timestamp
$stmt = $conn->prepare("SELECT id, filename, title, filepath, tags, uploaded_at, MD5(id) as version_hash 
                        FROM photos 
                        WHERE user_id = ? AND uploaded_at > ? 
                        ORDER BY uploaded_at DESC 
                        LIMIT 20");
$stmt->bind_param("is", $userId, $lastTimestamp);
$stmt->execute();
$result = $stmt->get_result();

$photos = [];
while ($row = $result->fetch_assoc()) {
    // Clean up the data for JSON
    $photos[] = [
        'id' => $row['id'],
        'filename' => htmlspecialchars($row['filename']),
        'title' => htmlspecialchars($row['title'] ?? $row['filename']),
        'filepath' => htmlspecialchars($row['filepath']),
        'tags' => !empty($row['tags']) ? explode(',', $row['tags']) : [],
        'uploaded_at' => $row['uploaded_at'],
        'version_hash' => $row['version_hash']
    ];
}

echo json_encode([
    'success' => true,
    'photos' => $photos,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>