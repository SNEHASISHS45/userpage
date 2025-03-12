<?php
// Database connection
include 'config.php';
include 'cloudinary_config.php';

require 'vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

// Initialize Cloudinary properly
$config = new Configuration([
    'cloud' => [
        'cloud_name' => 'dzn369qpk',
        'api_key'    => '274266766631951',
        'api_secret' => 'ThwRkNdXKQ2LKnQAAukKgmo510g',
    ],
    'url' => [
        'secure' => true
    ]
]);

$cloudinary = new Cloudinary($config);
$uploadApi = new UploadApi($config); // Create the upload API instance

// Check if tags column exists, if not create it
$check_column = $conn->query("SHOW COLUMNS FROM documents LIKE 'tags'");
if ($check_column->num_rows == 0) {
    // Column doesn't exist, add it
    $conn->query("ALTER TABLE documents ADD COLUMN tags VARCHAR(255) DEFAULT ''");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documents'])) {
    foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
        $file_name = pathinfo($_FILES['documents']['name'][$key], PATHINFO_FILENAME); // Auto-detect title
        
        // Check for duplicate uploads
        $check_sql = "SELECT * FROM documents WHERE title = '$file_name'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows == 0) {
            $upload = $uploadApi->upload($tmp_name, ['resource_type' => 'raw']);
            $file_url = $upload['secure_url'];
            
            // Use prepared statement to avoid SQL injection
            $sql = "INSERT INTO documents (title, url, tags, uploaded_at, version) VALUES (?, ?, '', NOW(), 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $file_name, $file_url);
            $stmt->execute();
        }
    }
}

if (isset($_POST['edit_title'])) {
    $id = $_POST['id'];
    $new_title = $conn->real_escape_string($_POST['new_title']);
    $sql = "UPDATE documents SET title='$new_title' WHERE id=$id";
    $conn->query($sql);
}

if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM documents WHERE id=$id";
    $conn->query($sql);
}

$search_query = "";
$filter_query = "";
if (isset($_GET['search'])) {
    $search_query = $conn->real_escape_string($_GET['search']);
    $filter_query = "WHERE title LIKE '%$search_query%'";
}

$sort_query = "ORDER BY uploaded_at DESC";
if (isset($_GET['sort'])) {
    if ($_GET['sort'] === 'name') {
        $sort_query = "ORDER BY title ASC";
    } elseif ($_GET['sort'] === 'date') {
        $sort_query = "ORDER BY uploaded_at DESC";
    }
}

$documents = $conn->query("SELECT * FROM documents $filter_query $sort_query");
?>




<!DOCTYPE html>
<html>
<head>
    <title>Document Manager</title>
    <link rel="stylesheet" href="css/documents/documents.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="script.js" defer></script>
</head>
<body>
        <div class="upload-area" id="upload-area">Drag & Drop Files Here</div>
        <form method="POST" enctype="multipart/form-data" id="upload-form">
            <input type="file" name="documents[]" id="file-input" multiple required hidden>
            <button class="btn1" type="submit">Upload</button>
        </form>
        <progress id="progress-bar" value="0" max="100" style="width: 100%; display: none;"></progress>

        <form method="GET" class="search-form">
            <input class="search-input" type="text" name="search" placeholder="Search by title" value="<?= htmlspecialchars($search_query) ?>">
            <button class="btn2" type="submit">Search</button>
        </form>
        <div class="sort-options">
            <label>Sort by:</label>
            <a href="?sort=name">Name</a> |
            <a href="?sort=date">Date</a>
        </div>
        <div class="container">
        <div class="row">
            <?php if ($documents && $documents->num_rows > 0): ?>
                <?php while ($row = $documents->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="document-card" ondblclick="openFullView('<?= urlencode($row['url']) ?>')">
                            <div class="iframe-container">
                                <div class="loader"></div>
                                <iframe src="https://mozilla.github.io/pdf.js/web/viewer.html?file=<?= urlencode($row['url']) ?>" onload="hideLoader(this)"></iframe>
                            </div>
                            <div class="document-options">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <div class="input-group">
                                        <input type="text" name="new_title" class="form-control" placeholder="New Title" required>
                                        <button type="submit" name="edit_title" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</button>
                                    </div>
                                </form>
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this document?')" class="btn btn-delete"><i class="fas fa-trash-alt"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="no-documents">
                        <i class="fas fa-file-alt fa-3x mb-3 text-muted"></i>
                        <h3>No documents found</h3>
                        <p class="text-muted">Upload your first document to get started</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

      <div id="full-view" class="full-view hidden" onclick="closeFullView(event)">
        <button class="close-btn" onclick="closeFullView()"><i class="fas fa-times"></i></button>
        <iframe id="full-view-frame" src="https://mozilla.github.io/pdf.js/web/viewer.html?file=<?= urlencode($row['url']) ?>" onload="hideLoader(this)"></iframe>
    </div>

<style>
    /* Enhanced iframe container styling */
    .iframe-container {
        position: relative;
        height: 250px;
        overflow: hidden;
        border-radius: 8px 8px 0 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        background-color: #f8f9fa;
    }
    
    .iframe-container iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: none; /* Hidden until loaded */
        transition: opacity 0.3s ease;
    }
    
    /* Improved loader styling */
    .loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #f8f9fa;
        z-index: 1;
    }
    
    .loader:after {
        content: '';
        width: 40px;
        height: 40px;
        border: 4px solid #e9ecef;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Full view improvements */
    .full-view {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
        z-index: 1000;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .full-view.hidden {
        display: none;
    }
    
    .full-view iframe {
        width: 90%;
        height: 90%;
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
        border-radius: 8px;
    }
    
    .close-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        background-color: #fff;
        color: #333;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        font-size: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        transition: all 0.2s ease;
    }
    
    .close-btn:hover {
        background-color: #f8f9fa;
        transform: scale(1.1);
    }
    
    /* Document card improvements */
    .document-card {
        margin-bottom: 30px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .document-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .document-options {
        padding: 15px;
        background-color: #fff;
        border-top: 1px solid #eee;
    }
</style>

<script>
    function hideLoader(iframe) {
        iframe.previousElementSibling.style.display = "none"; // Hide loader
        iframe.style.display = "block"; // Show iframe
        
        // Add fade-in effect
        iframe.style.opacity = 0;
        setTimeout(() => {
            iframe.style.opacity = 1;
        }, 50);
    }

    function openFullView(url) {
        document.getElementById("full-view").classList.remove("hidden");
        document.getElementById("full-view-frame").src = "https://mozilla.github.io/pdf.js/web/viewer.html?file=" + url;
    }

    function closeFullView() {
        document.getElementById("full-view").classList.add("hidden");
        document.getElementById("full-view-frame").src = "";
    }

    // Add event listener to make file upload work
    document.addEventListener('DOMContentLoaded', function() {
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('file-input');
        
        if (uploadArea && fileInput) {
            // Click on upload area to trigger file input
            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });
            
            // Drag and drop functionality
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', function() {
                this.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    // Optional: Show selected files
                    this.textContent = e.dataTransfer.files.length + ' file(s) selected';
                }
            });
            
            // Show selected files when chosen through the file input
            fileInput.addEventListener('change', function() {
                if (this.files.length) {
                    uploadArea.textContent = this.files.length + ' file(s) selected';
                }
            });
        }
    });
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</body>
</html>