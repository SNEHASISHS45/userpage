<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $upload_dir = 'profile_pics/';
    $file_name = basename($file['name']);
    $upload_file = $upload_dir . $file_name;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("File upload error: " . $file['error']);
    }

    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        die("File is not an image.");
    }

    if (move_uploaded_file($file['tmp_name'], $upload_file)) {
        $query = "UPDATE users SET profile_picture = :profile_picture WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':profile_picture', $file_name, PDO::PARAM_STR);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            header("Location: profile.php");
            exit();
        } else {
            die("Failed to update profile picture in database.");
        }
    } else {
        die("File upload failed.");
    }
}

// Fetch user profile information
$query = "SELECT username, profile_picture, bio FROM users WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user_info) {
    $username = $user_info['username'];
    $profile_picture = $user_info['profile_picture'];
    $bio = $user_info['bio'];
} else {
    die("User not found.");
}

$stmt->closeCursor();
$pdo = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
        <nav>
            <h1>Profile</h1>
            <div class="nav-container">
                
                <!-- Navigation Menu -->
                <ul id="nav-menu" class="nav-menu">
                    <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="dashboard.php"><i class="fa-solid fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                    <li><a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a></li>
                    <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
                </ul>

              

                <!-- Hamburger Menu -->
                <div class="nav-toggle" id="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>

    <div class="profile-section">
        <div class="profile-pic-container">
            <?php if ($profile_picture): ?>
                <img src="profile_pics/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic">
            <?php else: ?>
                <img src="default-profile.png" alt="Profile Picture" class="profile-pic">
            <?php endif; ?>
            <div class="edit-profile-photo">
                <button id="change-profile-photo-btn"><i class="fa-solid fa-camera"></i> Change Profile Picture</button>
                <form id="file-upload-form" method="post" enctype="multipart/form-data" style="display: none;">
                    <input type="file" id="file-upload" name="profile_picture" accept="image/*">
                    <input type="submit" value="Upload">
                </form>
            </div>
        </div>
        <div class="bio-section">
            <h3>Bio</h3>
            <p><?php echo htmlspecialchars($bio); ?></p>
            <button id="edit-bio-btn"><i class="fa-solid fa-edit"></i> Edit Bio</button>
        </div>
    </div>

    <div id="edit-bio-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Bio</h2>
                <span class="close-btn">&times;</span>
            </div>
            <form method="post">
                <textarea name="bio" rows="4" placeholder="Write something about yourself..." required><?php echo htmlspecialchars($bio); ?></textarea>
                <div class="modal-footer">
                    <button type="submit" name="update_bio" class="confirm"><i class="fa-solid fa-check"></i> Save</button>
                    <button type="button" class="cancel"><i class="fa-solid fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('change-profile-photo-btn').addEventListener('click', function() {
            document.getElementById('file-upload').click();
        });

        document.getElementById('file-upload').addEventListener('change', function() {
            document.getElementById('file-upload-form').submit();
        });

        var modal = document.getElementById("edit-bio-modal");
        var btn = document.getElementById("edit-bio-btn");
        var span = document.getElementsByClassName("close-btn")[0];
        var cancelBtn = document.querySelector(".modal-footer .cancel");

        btn.onclick = function() {
            modal.style.display = "block";
        }
        
        span.onclick = function() {
            modal.style.display = "none";
        }

        cancelBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
      <script>
        document.getElementById('nav-toggle').addEventListener('click', function () {
            const menu = document.getElementById('nav-menu');
            menu.classList.toggle('active');
        });
    </script>
</body>
</html>
