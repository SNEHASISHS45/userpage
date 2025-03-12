<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';
include 'cloudinary_config.php';

use Cloudinary\Api\Upload\UploadApi;

header('Content-Type: application/json');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => "You must be logged in to upload files"]));
}

$userId = $_SESSION['user_id'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'application/pdf', 'text/plain'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["photos"])) {
    $response = ['success' => true, 'files' => []];
    $errors = 0;
    
    // Handle single file upload (from AJAX)
    if (!is_array($_FILES["photos"]["name"])) {
        $tmpName = $_FILES["photos"]["tmp_name"];
        $fileName = basename($_FILES["photos"]["name"]);
        $fileType = mime_content_type($tmpName);

        if (!in_array($fileType, $allowedTypes)) {
            die(json_encode(['success' => false, 'message' => "Invalid file type: $fileType"]));
        }

        try {
            // Upload to Cloudinary with cache optimization
            $upload = (new UploadApi())->upload($tmpName, [
                'folder' => "sdrive_backup/$userId",
                'public_id' => pathinfo($fileName, PATHINFO_FILENAME),
                'resource_type' => 'auto',
                'cacheable' => true
            ]);
            $cloudinaryUrl = $upload['secure_url'];

            // Check for duplicate entries
            $checkStmt = $conn->prepare("SELECT id FROM photos WHERE filename = ? AND user_id = ?");
            $checkStmt->bind_param("si", $fileName, $userId);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows === 0) {
                $stmt = $conn->prepare("INSERT INTO photos (filename, user_id, filepath) VALUES (?, ?, ?)");
                $stmt->bind_param("sis", $fileName, $userId, $cloudinaryUrl);
                $stmt->execute();
                $newId = $conn->insert_id;
                $stmt->close();
                
                $response['files'][] = [
                    'name' => $fileName,
                    'url' => $cloudinaryUrl,
                    'id' => $newId
                ];
            } else {
                $response['files'][] = [
                    'name' => $fileName,
                    'message' => 'File already exists'
                ];
            }
            $checkStmt->close();
        } catch (Exception $e) {
            $errors++;
            $response['files'][] = [
                'name' => $fileName,
                'error' => $e->getMessage()
            ];
        }
    } else {
        // Handle multiple file uploads (from traditional form)
        foreach ($_FILES["photos"]["error"] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $tmpName = $_FILES["photos"]["tmp_name"][$key];
                $fileName = basename($_FILES["photos"]["name"][$key]);
                $fileType = mime_content_type($tmpName);

                if (!in_array($fileType, $allowedTypes)) {
                    $errors++;
                    $response['files'][] = [
                        'name' => $fileName,
                        'error' => "Invalid file type: $fileType"
                    ];
                    continue;
                }

                try {
                    // Upload to Cloudinary with cache optimization
                    $upload = (new UploadApi())->upload($tmpName, [
                        'folder' => "sdrive_backup/$userId",
                        'public_id' => pathinfo($fileName, PATHINFO_FILENAME),
                        'resource_type' => 'auto',
                        'cacheable' => true
                    ]);
                    $cloudinaryUrl = $upload['secure_url'];

                    // Check for duplicate entries
                    $checkStmt = $conn->prepare("SELECT id FROM photos WHERE filename = ? AND user_id = ?");
                    $checkStmt->bind_param("si", $fileName, $userId);
                    $checkStmt->execute();
                    $checkStmt->store_result();

                    if ($checkStmt->num_rows === 0) {
                        $stmt = $conn->prepare("INSERT INTO photos (filename, user_id, filepath) VALUES (?, ?, ?)");
                        $stmt->bind_param("sis", $fileName, $userId, $cloudinaryUrl);
                        $stmt->execute();
                        $newId = $conn->insert_id;
                        $stmt->close();
                        
                        $response['files'][] = [
                            'name' => $fileName,
                            'url' => $cloudinaryUrl,
                            'id' => $newId
                        ];
                    } else {
                        $response['files'][] = [
                            'name' => $fileName,
                            'message' => 'File already exists'
                        ];
                    }
                    $checkStmt->close();
                } catch (Exception $e) {
                    $errors++;
                    $response['files'][] = [
                        'name' => $fileName,
                        'error' => $e->getMessage()
                    ];
                }
            } else {
                $errors++;
                $response['files'][] = [
                    'name' => isset($_FILES["photos"]["name"][$key]) ? $_FILES["photos"]["name"][$key] : 'Unknown',
                    'error' => "Upload error code: $error"
                ];
            }
        }
    }
    
    if ($errors > 0) {
        $response['success'] = false;
        $response['message'] = "Some files failed to upload";
    }
    
    echo json_encode($response);
    exit();
}

// Return error if no files were uploaded
echo json_encode(['success' => false, 'message' => 'No files were uploaded']);
?>