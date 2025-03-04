<?php
// Database connection
include 'config.php';
include 'cloudinary_config.php';

require 'vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;




if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documents'])) {
    foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
        $file_name = pathinfo($_FILES['documents']['name'][$key], PATHINFO_FILENAME); // Auto-detect title
        
        // Check for duplicate uploads
        $check_sql = "SELECT * FROM documents WHERE title = '$file_name'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows == 0) {
            $upload = $uploadApi->upload($tmp_name, ['resource_type' => 'raw']);
            $file_url = $upload['secure_url'];
            
            $sql = "INSERT INTO documents (title, url, tags, uploaded_at, version) VALUES ('$file_name', '$file_url', '', NOW(), 1)";
            $conn->query($sql);
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
        </div>
    </div>

      <div id="full-view" class="full-view hidden" onclick="closeFullView(event)">
        <button class="close-btn" onclick="closeFullView()"><i class="fas fa-times"></i></button>
        <iframe id="full-view-frame" src="https://mozilla.github.io/pdf.js/web/viewer.html?file=<?= urlencode($row['url']) ?>" onload="hideLoader(this)"></iframe>
    </div>

<script>
    function hideLoader(iframe) {
        iframe.previousElementSibling.style.display = "none"; // Hide loader
        iframe.style.display = "block"; // Show iframe
    }

    function openFullView(url) {
    document.getElementById("full-view").classList.remove("hidden");
    document.getElementById("full-view-frame").src = "https://mozilla.github.io/pdf.js/web/viewer.html?file=" + url;
}

function closeFullView() {
    document.getElementById("full-view").classList.add("hidden");
    document.getElementById("full-view-frame").src = "";
}
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</body>
</html>