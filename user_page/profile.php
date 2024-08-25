<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile picture change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_name = basename($_FILES['profile_picture']['name']);
    $target_file = $upload_dir . $file_name;
    $upload_ok = 1;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES['profile_picture']['tmp_name']);
    if ($check === false) {
        echo "File is not an image.";
        $upload_ok = 0;
    }

    if ($_FILES['profile_picture']['size'] > 5000000) {
        echo "Sorry, your file is too large.";
        $upload_ok = 0;
    }

    if ($file_type != 'jpg' && $file_type != 'jpeg' && $file_type != 'png' && $file_type != 'gif') {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $upload_ok = 0;
    }

    if ($upload_ok == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $update_query = "UPDATE users SET profile_picture = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            if ($update_stmt === false) {
                die("Prepare failed: " . $conn->error);
            }
            $update_stmt->bind_param("si", $target_file, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
            header("Location: profile.php");
            exit();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle bio update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_bio'])) {
    $new_bio = $_POST['bio'];
    
    $update_bio_query = "UPDATE users SET bio = ? WHERE id = ?";
    $update_bio_stmt = $conn->prepare($update_bio_query);
    if ($update_bio_stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $update_bio_stmt->bind_param("si", $new_bio, $user_id);
    $update_bio_stmt->execute();
    $update_bio_stmt->close();
    header("Location: profile.php");
    exit();
}

// Fetch user information
$query = "SELECT username, profile_picture, bio FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $profile_picture, $bio);
$stmt->fetch();
$stmt->close();
$conn->close();
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
        <h1>Profile</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fa-solid fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                <li><a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="profile-section">
        <div class="profile-pic-container">
            <?php if ($profile_picture): ?>
                <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic">
            <?php else: ?>
                <img src="default-profile.png" alt="Profile Picture" class="profile-pic">
            <?php endif; ?>
            <div class="edit-profile-photo">
                <button id="change-profile-photo-btn"><i class="fa-solid fa-camera"></i> Change Profile Picture</button>
                <form id="file-upload-form" method="post" enctype="multipart/form-data" style="display: none;">
                    <input type="file" id="file-upload" name="profile_picture" accept="image/*">
                    <input type="submit" name="change_profile_picture" value="Upload">
                </form>
            </div>
        </div>
        <div class="bio-section">
            <h3>Bio</h3>
            <p><?php echo htmlspecialchars($bio); ?></p>
            <button id="edit-bio-btn"><i class="fa-solid fa-edit"></i> Edit Bio</button>
        </div>
    </div>

    <!-- Bio Edit Modal -->
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
</body>
</html>
