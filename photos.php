<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';
include 'cloudinary_config.php';

use Cloudinary\Api\Upload\UploadApi;

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}

$userId = $_SESSION['user_id'];

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'application/pdf', 'text/plain'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["photos"])) {
    foreach ($_FILES["photos"]["tmp_name"] as $key => $tmpName) {
        $fileName = basename($_FILES["photos"]["name"][$key]);
        $fileType = mime_content_type($tmpName);

        if (!in_array($fileType, $allowedTypes)) {
            echo "Invalid file type: $fileType";
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
                $stmt->close();
            }
            $checkStmt->close();
        } catch (Exception $e) {
            echo "Upload error: " . $e->getMessage();
        }
    }

    header("Location: index.php");
    exit();
}

// Handle Image Deletion
if (isset($_POST['delete_id'])) {
    $photoId = $_POST['delete_id'];

    $stmt = $conn->prepare("SELECT filepath FROM photos WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $photoId, $userId);
    $stmt->execute();
    $stmt->bind_result($filePath);
    $stmt->fetch();
    $stmt->close();

    if ($filePath) {
        // Extract Cloudinary Public ID
        $publicId = basename(parse_url($filePath, PHP_URL_PATH));
        $publicId = str_replace(['sdrive_backup/', '.jpg', '.png', '.webp', '.gif', '.mp4', '.pdf'], '', $publicId);

        // Delete from Cloudinary
        (new UploadApi())->destroy("sdrive_backup/$userId/$publicId");

        $stmt = $conn->prepare("DELETE FROM photos WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $photoId, $userId);
        $stmt->execute();
        $stmt->close();
    }

    exit();
}

// Handle Title Update
if (isset($_POST['update_title'])) {
    $photoId = $_POST['update_id'];
    $newTitle = htmlspecialchars($_POST['new_title']);

    $stmt = $conn->prepare("UPDATE photos SET title = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $newTitle, $photoId, $userId);
    $stmt->execute();
    $stmt->close();

    exit();
}

// Add this after your existing database operations

// Handle adding tags
if (isset($_POST['add_tag'])) {
    $photoId = $_POST['photo_id'];
    $tag = htmlspecialchars(trim($_POST['tag']));
    
    if (!empty($tag)) {
        // Check if tags column exists
        $checkColumn = $conn->query("SHOW COLUMNS FROM photos LIKE 'tags'");
        if ($checkColumn->num_rows == 0) {
            // Column doesn't exist, add it
            $conn->query("ALTER TABLE photos ADD COLUMN tags TEXT DEFAULT NULL");
        }
        
        // Get current tags
        $stmt = $conn->prepare("SELECT tags FROM photos WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $photoId, $userId);
        $stmt->execute();
        $stmt->bind_result($currentTags);
        $stmt->fetch();
        $stmt->close();
        
        // Parse tags and add new one if it doesn't exist
        $tagsArray = !empty($currentTags) ? explode(',', $currentTags) : [];
        if (!in_array($tag, $tagsArray)) {
            $tagsArray[] = $tag;
            $newTags = implode(',', $tagsArray);
            
            $stmt = $conn->prepare("UPDATE photos SET tags = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sii", $newTags, $photoId, $userId);
            $stmt->execute();
            $stmt->close();
            
            echo json_encode(['success' => true, 'tags' => $tagsArray]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tag already exists']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tag cannot be empty']);
    }
    exit();
}

// Handle removing tags
if (isset($_POST['remove_tag'])) {
    $photoId = $_POST['photo_id'];
    $tag = htmlspecialchars(trim($_POST['tag']));
    
    // Get current tags
    $stmt = $conn->prepare("SELECT tags FROM photos WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $photoId, $userId);
    $stmt->execute();
    $stmt->bind_result($currentTags);
    $stmt->fetch();
    $stmt->close();
    
    if (!empty($currentTags)) {
        $tagsArray = explode(',', $currentTags);
        $key = array_search($tag, $tagsArray);
        
        if ($key !== false) {
            unset($tagsArray[$key]);
            $newTags = implode(',', $tagsArray);
            
            $stmt = $conn->prepare("UPDATE photos SET tags = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sii", $newTags, $photoId, $userId);
            $stmt->execute();
            $stmt->close();
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tag not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No tags found']);
    }
    exit();
}

// Get all unique tags for filter
$allTags = [];
$tagQuery = $conn->prepare("SELECT DISTINCT tags FROM photos WHERE user_id = ? AND tags IS NOT NULL AND tags != ''");
$tagQuery->bind_param("i", $userId);
$tagQuery->execute();
$tagResult = $tagQuery->get_result();

while ($tagRow = $tagResult->fetch_assoc()) {
    if (!empty($tagRow['tags'])) {
        $photoTags = explode(',', $tagRow['tags']);
        foreach ($photoTags as $tag) {
            if (!empty($tag) && !in_array($tag, $allTags)) {
                $allTags[] = $tag;
            }
        }
    }
}
sort($allTags);

// Filter by tag if requested
$tagFilter = isset($_GET['tag']) ? htmlspecialchars($_GET['tag']) : '';
if (!empty($tagFilter)) {
    $stmt = $conn->prepare("SELECT *, MD5(id) as version_hash FROM photos WHERE user_id = ? AND tags LIKE ? ORDER BY uploaded_at DESC");
    $tagParam = "%$tagFilter%";
    $stmt->bind_param("is", $userId, $tagParam);
} else {
    // Use your existing query
    $stmt = $conn->prepare("SELECT *, MD5(id) as version_hash FROM photos WHERE user_id = ? ORDER BY uploaded_at DESC");
    $stmt->bind_param("i", $userId);
}
$stmt->execute();
$result = $stmt->get_result();
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery</title>
    <link href="https://fonts.googleapis.com/css2?family=Reem+Kufi&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Rounded" rel="stylesheet">
    <link rel="stylesheet" href="css/photos/photos.css">
    <link rel="stylesheet" href="css/photos/photo-tags.css">
    <link rel="stylesheet" href="css/photos/message-popup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Add preload for images -->
    <?php 
    // Reset result pointer
    if ($result->num_rows > 0) {
        $preloadCount = 0;
        // Store rows for later reuse
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
            if ($preloadCount < 5) {
                echo '<link rel="preload" as="image" href="' . htmlspecialchars($row['filepath'] . '?w=300&h=300&c=fill&f_auto&q_auto') . '">';
                $preloadCount++;
            }
        }
        // Reset for main loop
        $result->data_seek(0);
    }
    ?>
</head>

<body>
    <section class="gallery">
        <!-- Add tag filter dropdown -->
        <div class="filter-container">
            <div class="tag-filter">
                <label for="tag-filter">Filter by tag:</label>
                <select id="tag-filter" onchange="filterByTag(this.value)">
                    <option value="">All Photos</option>
                    <?php foreach ($allTags as $tag): ?>
                        <option value="<?= htmlspecialchars($tag) ?>" <?= ($tagFilter === $tag) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tag) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($tagFilter)): ?>
                    <a href="photos.php" class="clear-filter">Clear Filter</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="upload-form">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="file" name="photos[]" multiple required>
                <button type="submit">Upload</button>
            </form>
        </div>
        
        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                $colCount = 0;
                $imagesPerCol = ceil($result->num_rows / 4);
                echo '<div class="col">';

                while ($row = $result->fetch_assoc()):
                    if ($colCount && $colCount % $imagesPerCol === 0) {
                        echo '</div><div class="col">';
                    }
                    
                    // Add version hash for cache busting
                    $cacheBuster = isset($row['version_hash']) ? '&v=' . $row['version_hash'] : '';
                    ?>
                    <div class="fluid-container">
                        <div class="item">
                            <div class="img">
                                <img 
                                    src="<?= htmlspecialchars($row['filepath'] . '?w=300&h=300&c=fill&f_auto&q_auto&dpr=auto' . $cacheBuster) ?>"
                                    alt="Gallery Image" 
                                    data-id="<?= $row['id'] ?>" 
                                    data-full-src="<?= htmlspecialchars($row['filepath'] . '?f_auto&q_auto' . $cacheBuster) ?>"
                                    loading="lazy">
                            </div>
                            <div class="info">
                                <span class="title"><?= htmlspecialchars($row['title'] ?? $row['filename']) ?></span>
                                
                                <!-- Tags display -->
                                <div class="tags-container" data-photo-id="<?= $row['id'] ?>">
                                    <?php 
                                    // Parse tags for this specific photo
                                    $photoTags = !empty($row['tags']) ? explode(',', $row['tags']) : [];
                                    
                                    foreach ($photoTags as $tag): ?>
                                        <?php if (!empty($tag)): ?>
                                            <span class="tag">
                                                <?= htmlspecialchars($tag) ?>
                                                <span class="remove-tag" data-tag="<?= htmlspecialchars($tag) ?>">×</span>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <button class="add-tag-btn" data-photo-id="<?= $row['id'] ?>">+ Add Tag</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $colCount++;
                endwhile;
                echo '</div>';
            } else {
                echo '<p>No images found. Upload some!</p>';
            }
            ?>
        </div>
    </section>

    <div class="overlay">
        <div class="viewer">
            <div>
                <div class="alt">Image Preview</div>
                <div class="viewer-actions">
                    <button class="edit-title-viewer" style="background-color: transparent; border: none; color: white; cursor: pointer; margin-right: 10px;">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="delete-viewer" style="background-color: transparent; border: none; color: white; cursor: pointer; margin-right: 10px;">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                    <span class="material-symbols-rounded close"></span>
                </div>
            </div>
            <div>
                <img src="" alt="Preview">
            </div>
            <div class="viewer-tags">
                <!-- Tags will be populated by JavaScript -->
                <button class="add-viewer-tag">+ Add Tag</button>
            </div>
        </div>
    </div>

    <script>
        // Implement Intersection Observer for lazy loading
        document.addEventListener("DOMContentLoaded", function() {
            let images = document.querySelectorAll(".item img");
            let currentPhotoId = null;
            const viewer = document.querySelector(".viewer img");
            
            // Initialize Intersection Observer
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const container = entry.target.closest('.fluid-container');
                        if (container) {
                            container.classList.add("inScreen");
                        }
                        // Stop observing after the element becomes visible
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: "0px 0px 300px 0px" });
            
            // Start observing all images
            images.forEach(img => {
                imageObserver.observe(img);
            });
            
            document.querySelector(".viewer .close").onclick = () => {
                document.querySelector("body").classList.toggle("overlayed");
            };
            
            // Add click event to the overlay to close when clicking outside
            document.querySelector(".overlay").addEventListener("click", function(e) {
                // Only close if the click is directly on the overlay, not on its children
                if (e.target === this) {
                    document.querySelector("body").classList.remove("overlayed");
                }
            });
            
            // Item click event
            document.querySelectorAll(".item").forEach((item) => {
                item.onclick = () => {
                    let imgElement = item.querySelector(".img img");
                    let photoId = imgElement.getAttribute("data-id");
                    currentPhotoId = photoId;
                    
                    // Use full resolution image for the viewer
                    let fullSrc = imgElement.getAttribute("data-full-src");
                    viewer.setAttribute("src", fullSrc || imgElement.getAttribute("src"));
                    
                    document.querySelector(".viewer .alt").innerHTML = item.querySelector(
                        ".title"
                    ).innerHTML;
                    
                    // Update viewer tags
                    updateViewerTags(photoId);
                    
                    document.querySelector("body").classList.toggle("overlayed");
                };
            });
            
            // Edit title in viewer
            document.querySelector(".edit-title-viewer").onclick = (e) => {
                e.stopPropagation();
                if (!currentPhotoId) return;
                
                let title = prompt("Enter new title:");
                if (title) {
                    // Use FormData to properly encode the data
                    let formData = new FormData();
                    formData.append('update_title', 'true');
                    formData.append('update_id', currentPhotoId);
                    formData.append('new_title', title);
                    
                    fetch('photos.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // Update title in viewer
                        document.querySelector(".viewer .alt").innerHTML = title;
                        
                        // Also update title in thumbnail
                        let thumbnailImg = document.querySelector(`img[data-id="${currentPhotoId}"]`);
                        if (thumbnailImg) {
                            let thumbnailTitle = thumbnailImg.closest('.item').querySelector(".title");
                            if (thumbnailTitle) {
                                thumbnailTitle.innerHTML = title;
                            }
                        }
                        
                        // Show success message
                        showMessage('Title updated successfully', 'success');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('Failed to update title', 'error');
                    });
                }
            };
            
            // Delete in viewer
            document.querySelector(".delete-viewer").onclick = (e) => {
                e.stopPropagation();
                if (!currentPhotoId) return;
                
                if (confirm("Are you sure you want to delete this image?")) {
                    let formData = new FormData();
                    formData.append('delete_id', currentPhotoId);
                    
                    fetch('photos.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // Close viewer
                        document.querySelector("body").classList.remove("overlayed");
                        
                        // Remove thumbnail
                        let thumbnailImg = document.querySelector(`img[data-id="${currentPhotoId}"]`);
                        if (thumbnailImg) {
                            let container = thumbnailImg.closest('.fluid-container');
                            if (container) {
                                container.remove();
                            }
                        }
                        
                        // Show success message
                        showMessage('Image deleted successfully', 'success');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('Failed to delete image', 'error');
                    });
                }
            };
            
            // Keyboard support
            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape" && document.querySelector("body").classList.contains("overlayed")) {
                    document.querySelector("body").classList.remove("overlayed");
                }
            });
            
            // Tag functionality
            const tagModal = document.querySelector('.tag-input-modal');
            const tagInput = document.getElementById('tag-input');
            const tagPhotoId = document.getElementById('tag-photo-id');
            const tagSaveBtn = document.querySelector('.tag-input-save');
            const tagCancelBtn = document.querySelector('.tag-input-cancel');
            const tagCloseBtn = document.querySelector('.tag-input-close');
            const tagSuggestions = document.getElementById('tag-suggestions');
            
            // Get all existing tags for suggestions
            const existingTags = <?= json_encode($allTags) ?>;
            
            // Filter by tag function
            window.filterByTag = function(tag) {
                if (tag) {
                    window.location.href = 'index.php?tag=' + encodeURIComponent(tag);
                } else {
                    window.location.href = 'index.php';
                }
            };
            
            // Add tag button click event
            document.querySelectorAll('.add-tag-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const photoId = this.getAttribute('data-photo-id');
                    openTagModal(photoId);
                });
            });
            
            // Add tag in viewer
            document.querySelector('.add-viewer-tag').addEventListener('click', function(e) {
                e.stopPropagation();
                if (!currentPhotoId) return;
                openTagModal(currentPhotoId);
            });
            
            // Tag input suggestions
            tagInput.addEventListener('input', function() {
                const value = this.value.trim().toLowerCase();
                
                if (value.length < 1) {
                    tagSuggestions.style.display = 'none';
                    return;
                }
                
                // Filter matching tags
                const matchingTags = existingTags.filter(tag => 
                    tag.toLowerCase().includes(value) && 
                    tag.toLowerCase() !== value
                );
                
                if (matchingTags.length > 0) {
                    tagSuggestions.innerHTML = '';
                    matchingTags.forEach(tag => {
                        const item = document.createElement('div');
                        item.className = 'tag-suggestion-item';
                        item.textContent = tag;
                        item.addEventListener('click', function() {
                            tagInput.value = tag;
                            tagSuggestions.style.display = 'none';
                        });
                        tagSuggestions.appendChild(item);
                    });
                    tagSuggestions.style.display = 'block';
                } else {
                    tagSuggestions.style.display = 'none';
                }
            });
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== tagInput && e.target !== tagSuggestions) {
                    tagSuggestions.style.display = 'none';
                }
            });
            
            // Rest of your tag-related code...
            
            // Function to update thumbnail tags - enhanced version
            function updateThumbnailTags(photoId, tags) {
                const tagsContainer = document.querySelector(`.tags-container[data-photo-id="${photoId}"]`);
                if (!tagsContainer) return;
                
                // Clear existing tags except the add button
                const addButton = tagsContainer.querySelector('.add-tag-btn');
                tagsContainer.innerHTML = '';
                
                // Add new tags
                tags.forEach(tag => {
                    if (tag.trim()) {
                        const tagElement = document.createElement('span');
                        tagElement.className = 'tag';
                        
                        // Count photos with this tag
                        const tagCount = document.querySelectorAll(`.tag:contains("${tag}")`).length + 1;
                        
                        tagElement.innerHTML = `
                            ${tag}
                            ${tagCount > 1 ? `<span class="tag-count">${tagCount}</span>` : ''}
                            <span class="remove-tag" data-tag="${tag}">×</span>
                        `;
                        
                        // Add click event to filter by this tag
                        tagElement.addEventListener('click', function(e) {
                            // Don't trigger if clicking on the remove button
                            if (!e.target.classList.contains('remove-tag')) {
                                filterByTag(tag);
                            }
                        });
                        
                        tagsContainer.appendChild(tagElement);
                    }
                });
                
                // Add the button back
                tagsContainer.appendChild(addButton);
            }
            
            // Function to update viewer tags - enhanced version
            function updateViewerTags(photoId) {
                // Get tags from thumbnail
                const tagsContainer = document.querySelector(`.tags-container[data-photo-id="${photoId}"]`);
                const viewerTagsContainer = document.querySelector('.viewer-tags');
                
                // Clear existing viewer tags except the add button
                const addButton = viewerTagsContainer.querySelector('.add-viewer-tag');
                viewerTagsContainer.innerHTML = '';
                
                // Clone tags from thumbnail
                if (tagsContainer) {
                    const tags = tagsContainer.querySelectorAll('.tag');
                    tags.forEach(tag => {
                        const tagText = tag.textContent.replace('×', '').trim();
                        // Remove any numbers (from tag count)
                        const cleanTagText = tagText.replace(/\d+/g, '').trim();
                        
                        const viewerTag = document.createElement('span');
                        viewerTag.className = 'viewer-tag';
                        viewerTag.textContent = cleanTagText;
                        
                        // Add click event to filter by this tag
                        viewerTag.addEventListener('click', function() {
                            document.querySelector("body").classList.remove("overlayed");
                            filterByTag(cleanTagText);
                        });
                        
                        viewerTagsContainer.appendChild(viewerTag);
                    });
                }
                
                // Add the button back
                viewerTagsContainer.appendChild(addButton);
            }
            
            // Add this helper function for tag filtering
            jQuery.expr[':'].contains = function(a, i, m) {
                return jQuery(a).text().toUpperCase()
                    .indexOf(m[3].toUpperCase()) >= 0;
            };
        });

        // Add this to your existing script section
document.addEventListener("DOMContentLoaded", function() {
    // Photo full view functionality
    const photoItems = document.querySelectorAll('.photo-item');
    

    // Add click event to each photo
    photoItems.forEach((item, index) => {
        item.addEventListener('click', () => {
            const modal = document.querySelector('.photo-modal');
            const modalImg = modal.querySelector('.photo-modal-content img');
            const img = item.querySelector('img');
            
            modalImg.src = img.src;
            currentIndex = index;
            modal.classList.add('active');
        });
    });
});
    </script>
    
    <!-- Tag Input Modal -->
    <div class="tag-input-modal">
        <div class="tag-input-container">
            <div class="tag-input-header">
                <h3>Add Tag</h3>
                <button class="tag-input-close">&times;</button>
            </div>
            <div class="tag-input-form">
                <input type="text" id="tag-input" placeholder="Enter tag name" autocomplete="off">
                <div id="tag-suggestions" class="tag-suggestions"></div>
                <input type="hidden" id="tag-photo-id">
                <div class="tag-input-actions">
                    <button class="tag-input-cancel">Cancel</button>
                    <button class="tag-input-save">Save</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>