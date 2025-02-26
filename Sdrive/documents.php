<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'config.php';
require 'vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;

// Initialize Cloudinary
$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dzn369qpk',
        'api_key'    => '274266766631951',
        'api_secret' => 'ThwRkNdXKQ2LKnQAAukKgmo510g',
    ],
    'url' => [
        'secure' => true // Ensure HTTPS URLs
    ]
]);

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_FILES["document"])) {
        $file = $_FILES["document"];

        try {
            $upload = $cloudinary->uploadApi()->upload($file['tmp_name'], [
                'folder' => 'sdrive_backup/user_' . $user_id . '/documents',
                'resource_type' => 'auto',
                'public_id' => pathinfo($file['name'], PATHINFO_FILENAME)
            ]);

            $filename = $upload['original_filename'] . '.' . $upload['format'];
            $file_url = $upload['secure_url'];

            $stmt = $conn->prepare("INSERT INTO documents (user_id, filename, filepath) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $filename, $file_url);
            $stmt->execute();
            $stmt->close();

        } catch (Exception $e) {
            die("Upload Error: " . $e->getMessage());
        }
    }

    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];

        $stmt = $conn->prepare("SELECT filepath FROM documents WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $delete_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($file_url);
        $stmt->fetch();
        $stmt->close();

        if ($file_url) {
            preg_match("/\/([^\/]+)\.(\w+)$/", $file_url, $matches);
            $public_id = 'sdrive_backup/user_' . $user_id . '/documents/' . $matches[1];

            try {
                $cloudinary->uploadApi()->destroy($public_id, ['resource_type' => 'auto']);

                $stmt = $conn->prepare("DELETE FROM documents WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $delete_id, $user_id);
                $stmt->execute();
                $stmt->close();

            } catch (Exception $e) {
                die("Delete Error: " . $e->getMessage());
            }
        }
    }

    if (isset($_POST['rename_id']) && isset($_POST['new_name'])) {
        $rename_id = $_POST['rename_id'];
        $new_name = basename($_POST['new_name']);

        $stmt = $conn->prepare("SELECT filepath FROM documents WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $rename_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($file_url);
        $stmt->fetch();
        $stmt->close();

        if ($file_url) {
            preg_match("/\/([^\/]+)\.(\w+)$/", $file_url, $matches);
            $old_public_id = 'sdrive_backup/user_' . $user_id . '/documents/' . $matches[1];
            $new_public_id = 'sdrive_backup/user_' . $user_id . '/documents/' . pathinfo($new_name, PATHINFO_FILENAME);

            try {
                $upload = $cloudinary->uploadApi()->rename($old_public_id, $new_public_id, ['resource_type' => 'auto']);
                $new_url = $upload['secure_url'];

                $stmt = $conn->prepare("UPDATE documents SET filename = ?, filepath = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ssii", $new_name, $new_url, $rename_id, $user_id);
                $stmt->execute();
                $stmt->close();

            } catch (Exception $e) {
                die("Rename Error: " . $e->getMessage());
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Document Backup</title>
    <link rel="stylesheet" href="css/documents/documents.css">
</head>
<body>
    <h3>Upload a Document</h3>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="document" required>
        <button type="submit">Upload</button>
    </form>

    <h3>Your Documents</h3>
    <?php while ($doc = $result->fetch_assoc()): ?>
        <div>
            <p><?php echo htmlspecialchars($doc['filename']); ?></p>
            <a href="<?php echo htmlspecialchars($doc['filepath']); ?>" target="_blank">View</a>
            <a href="<?php echo htmlspecialchars($doc['filepath']); ?>" download>Download</a>

            <form method="post">
                <input type="hidden" name="delete_id" value="<?php echo $doc['id']; ?>">
                <button type="submit">Delete</button>
            </form>

            <form method="post">
                <input type="hidden" name="rename_id" value="<?php echo $doc['id']; ?>">
                <input type="text" name="new_name" placeholder="New name" required>
                <button type="submit">Rename</button>
            </form>
        </div>
    <?php endwhile; ?>
</body>
</html>
