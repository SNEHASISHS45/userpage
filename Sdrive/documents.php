<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}

$user_id = $_SESSION['user_id'];
$user_folder = "uploads/user_" . $user_id . "/";
if (!is_dir($user_folder)) {
    mkdir($user_folder, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_FILES["document"])) {
        $file = $_FILES["document"];
        $filename = basename($file["name"]);
        $filepath = $user_folder . time() . "_" . $filename;

        $checkStmt = $conn->prepare("SELECT id FROM documents WHERE user_id = ? AND filename = ?");
        $checkStmt->bind_param("is", $user_id, $filename);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows === 0) {
            if (move_uploaded_file($file["tmp_name"], $filepath)) {
                $stmt = $conn->prepare("INSERT INTO documents (user_id, filename, filepath) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user_id, $filename, $filepath);
                $stmt->execute();
                $stmt->close();
            }
        }
        $checkStmt->close();
    }

    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        $stmt = $conn->prepare("SELECT filepath FROM documents WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $delete_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($filepath);
        $stmt->fetch();
        $stmt->close();
        
        if ($filepath && file_exists($filepath)) {
            unlink($filepath);
        }
        
        $stmt = $conn->prepare("DELETE FROM documents WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $delete_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['rename_id']) && isset($_POST['new_name'])) {
        $rename_id = $_POST['rename_id'];
        $new_name = basename($_POST['new_name']);
        
        $stmt = $conn->prepare("SELECT filepath FROM documents WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $rename_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($old_filepath);
        $stmt->fetch();
        $stmt->close();
        
        $new_filepath = $user_folder . time() . "_" . $new_name;
        
        if (rename($old_filepath, $new_filepath)) {
            $stmt = $conn->prepare("UPDATE documents SET filename = ?, filepath = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssii", $new_name, $new_filepath, $rename_id, $user_id);
            $stmt->execute();
            $stmt->close();
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Backup</title>
    <link rel="stylesheet" href="css/documents/documents.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
   
        
        <section class="upload-section">
            <h3>Upload a Document</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="document" required>
                <button type="submit" id="upload-btn"><i class="fas fa-upload"></i> Upload</button>
            </form>
        </section>

        <section class="documents-section">
            <h3>Your Documents</h3>
            <div class="document-list">
                <?php while ($doc = $result->fetch_assoc()): ?>
                    <div class="document-item">
                        <p><?php echo htmlspecialchars($doc['filename']); ?></p>
                        <div class="document-preview">
                            <?php
                            $file_extension = strtolower(pathinfo($doc['filename'], PATHINFO_EXTENSION));
                            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                echo '<img src="'.htmlspecialchars($doc['filepath']).'" alt="Preview">';
                            } elseif ($file_extension === 'pdf') {
                                echo '<embed src="'.htmlspecialchars($doc['filepath']).'" type="application/pdf">';
                            } else {
                                echo '<i class="fas fa-file-alt"></i>';
                            }
                            ?>
                        </div>
                        <div class="actions">
                            <a href="<?php echo htmlspecialchars($doc['filepath']); ?>" target="_blank" class="view"><i class="fas fa-eye"></i></a>
                            <a href="<?php echo htmlspecialchars($doc['filepath']); ?>" download class="download"><i class="fas fa-download"></i></a>
                            <form method="post" class="delete-form">
                                <input type="hidden" name="delete_id" value="<?php echo $doc['id']; ?>">
                                <button type="submit" class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                            </form>
                            <form method="post" class="rename-form">
                                <input type="hidden" name="rename_id" value="<?php echo $doc['id']; ?>">
                                <input type="text" name="new_name" placeholder="New name" required>
                                <button type="submit" class="rename-btn"><i class="fas fa-edit"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>
</body>
</html>
