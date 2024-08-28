<?php
session_start();
include 'db_connect.php'; // Ensure this file includes $pdo

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';

// Handle document upload
if (isset($_POST['upload_document'])) {
    if (isset($_FILES['document']) && !empty(array_filter($_FILES['document']['name']))) {
        $file_count = count($_FILES['document']['name']);
        $target_dir = "uploads/documents/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        for ($i = 0; $i < $file_count; $i++) {
            $original_file_name = basename($_FILES["document"]["name"][$i]);
            $unique_file_name = uniqid() . '-' . $original_file_name;
            $target_file = $target_dir . $unique_file_name;
            $uploadOk = 1;
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check file size (10MB limit)
            if ($_FILES["document"]["size"][$i] > 10000000) {
                $uploadOk = 0;
                $error_message .= "File " . htmlspecialchars($original_file_name) . " is too large.<br>";
                continue; // Skip this file
            }

            // Allow only specific file formats
            if (!in_array($fileType, ["pdf", "doc", "docx", "xls", "xlsx"])) {
                $uploadOk = 0;
                $error_message .= "Only PDF, DOC, DOCX, XLS & XLSX files are allowed for " . htmlspecialchars($original_file_name) . ".<br>";
                continue; // Skip this file
            }

            // Check if the file already exists in the database
            $query = "SELECT COUNT(*) FROM documents WHERE user_id = :user_id AND file_path = :file_path";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':file_path', $target_file);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                $uploadOk = 0;
                $error_message .= "File " . htmlspecialchars($original_file_name) . " already exists.<br>";
                continue; // Skip this file
            }

            // Determine category (default to "Regular" if not specified)
            $category = isset($_POST['category']) ? $_POST['category'] : 'Regular';

            // If everything is ok, try to upload the file
            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["document"]["tmp_name"][$i], $target_file)) {
                    // Insert document information into the database using PDO
                    try {
                        $query = "INSERT INTO documents (user_id, file_path, category) VALUES (:user_id, :file_path, :category)";
                        $stmt = $pdo->prepare($query);
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->bindParam(':file_path', $target_file);
                        $stmt->bindParam(':category', $category);
                        $stmt->execute();
                    } catch (PDOException $e) {
                        $error_message .= "Database error: " . $e->getMessage() . "<br>";
                    }
                } else {
                    $error_message .= "Sorry, there was an error uploading your file " . htmlspecialchars($original_file_name) . ".<br>";
                }
            }
        }

        // Redirect to the same page to avoid re-upload on refresh
        header("Location: documents.php");
        exit();
    } else {
        $error_message = "No files were uploaded.";
    }
}

// Handle document deletion
if (isset($_GET['delete_document_id'])) {
    $document_id = intval($_GET['delete_document_id']);

    // Fetch document paths using PDO
    $query = "SELECT file_path FROM documents WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $document_id);
    $stmt->execute();
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($document) {
        // Delete document from database
        $query = "DELETE FROM documents WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $document_id);
        $stmt->execute();

        // Delete document file
        if (file_exists($document['file_path'])) {
            unlink($document['file_path']);
        }
    }
}

// Handle multiple document deletions
if (isset($_GET['document_ids'])) {
    $document_ids = explode(',', $_GET['document_ids']);

    foreach ($document_ids as $document_id) {
        $document_id = intval($document_id);

        // Fetch document paths using PDO
        $query = "SELECT file_path FROM documents WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $document_id);
        $stmt->execute();
        $document = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($document) {
            // Delete document from database
            $query = "DELETE FROM documents WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $document_id);
            $stmt->execute();

            // Delete document file
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }
        }
    }
}

// Fetch user's important and regular documents from the database using PDO
$query = "SELECT id, file_path, category FROM documents WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdo = null; // Close PDO connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Documents</title>
    <link rel="stylesheet" href="documents.css">
</head>
<body>
    <header>
    <nav>
    <ul>
        <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
        <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

    </header>

    <div class="content">
        <section class="documents">
            <h1>Your Documents</h1>

            <!-- Upload Document Form -->
            <div class="upload-form">
                <h2>Upload Document</h2>
                <form action="documents.php" method="post" enctype="multipart/form-data">
                    <input type="file" id="documentInput" name="document[]" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple>
                    <select name="category" id="categorySelect" required>
                        <option value="Regular">Regular</option>
                        <option value="Important">Important</option>
                    </select>
                    <button type="button" id="chooseDocumentsButton">Choose Documents</button>
                    <button type="submit" name="upload_document" id="uploadDocumentsButton" style="display: none;">Upload Documents</button>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                </form>
            </div>

            <!-- Select All & Delete Multiple Form -->
            <div class="select-all-container">
                <form id="deleteMultipleForm" action="documents.php" method="get">
                    <input type="checkbox" id="selectAllCheckbox">
                    <label for="selectAllCheckbox">Select All</label>
                    <button type="submit" class="delete-multiple-button" id="deleteMultipleButton">Delete Selected</button>
                    <input type="hidden" name="document_ids" id="documentIdsInput">
                </form>
            </div>

            <!-- Modal for displaying the full document -->
            <div id="document-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <iframe id="modal-iframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>

            <!-- Document List -->
            <div class="document-list">
                <!-- Important Documents -->
                <div class="document-category important-documents">
                    <h2>Important Documents</h2>
                    <div class="documents-container">
                        <?php foreach ($documents as $document): ?>
                            <?php if ($document['category'] === 'Important'): ?>
                                <div class="document-card">
                                    <a href="<?php echo htmlspecialchars($document['file_path']); ?>" target="_blank" class="view-link">View Document</a>
                                    <input type="checkbox" class="document-checkbox" data-document-id="<?php echo $document['id']; ?>">
                                    <a href="documents.php?delete_document_id=<?php echo $document['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this document?')">Delete</a>
                                    <div class="document-preview">
                                        <iframe src="<?php echo htmlspecialchars($document['file_path']); ?>" frameborder="0"></iframe>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Regular Documents -->
                <div class="document-category regular-documents">
                    <h2>Regular Documents</h2>
                    <div class="documents-container">
                        <?php foreach ($documents as $document): ?>
                            <?php if ($document['category'] === 'Regular'): ?>
                                <div class="document-card">
                                    <a href="<?php echo htmlspecialchars($document['file_path']); ?>" target="_blank" class="view-link">View Document</a>
                                    <input type="checkbox" class="document-checkbox" data-document-id="<?php echo $document['id']; ?>">
                                    <a href="documents.php?delete_document_id=<?php echo $document['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this document?')">Delete</a>
                                    <div class="document-preview">
                                        <iframe src="<?php echo htmlspecialchars($document['file_path']); ?>" frameborder="0"></iframe>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', (event) => {
        const chooseButton = document.getElementById('chooseDocumentsButton');
        const uploadButton = document.getElementById('uploadDocumentsButton');
        const fileInput = document.getElementById('documentInput');

        // Trigger file input dialog when 'Choose Documents' button is clicked
        chooseButton.addEventListener('click', function() {
            fileInput.click(); // Trigger file input dialog
        });

        // Show upload button when files are selected
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                uploadButton.style.display = 'inline'; // Show the upload button
                chooseButton.style.display = 'none'; // Hide the choose button
            } else {
                uploadButton.style.display = 'none'; // Hide the upload button if no files are selected
                chooseButton.style.display = 'inline'; // Show the choose button
            }
        });

        // Select All Checkbox functionality
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.document-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Delete Multiple Button functionality
        const deleteButton = document.getElementById('deleteMultipleButton');
        deleteButton.addEventListener('click', function(event) {
            const selectedCheckboxes = document.querySelectorAll('.document-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one document to delete.');
                event.preventDefault();
            } else {
                const documentIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.getAttribute('data-document-id')).join(',');
                document.getElementById('documentIdsInput').value = documentIds;
            }
        });

        // Modal functionality
        const modal = document.getElementById("document-modal");
        const modalIframe = document.getElementById("modal-iframe");
        const closeModal = document.getElementsByClassName("close")[0];

        // Open modal on iframe click
        document.querySelectorAll('.document-preview iframe').forEach(iframe => {
            iframe.addEventListener('click', function() {
                modal.style.display = "block";
                modalIframe.src = this.src; // Load the same source into the modal iframe
            });
        });

        // Close modal
        closeModal.addEventListener('click', function() {
            modal.style.display = "none";
        });

        // Close modal when clicking outside of the modal
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    });
    </script>

</body>
</html>
