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
    <link rel="stylesheet" href="styles.css">
    <style>
body {
    font-family: 'Helvetica Neue', Arial, sans-serif;
    background: #000; /* Dark background */
    color: #e0e0e0; /* Light text color for contrast */
    padding: 20px;
    margin: 0;
    animation: fadeInBody 1s ease-out;
}

header {
    background: #1c1c1c; /* Dark header background */
    color: #e0e0e0;
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #333;
    animation: slideInHeader 1s ease-out;
}

header h1 {
    margin: 0;
    font-size: 24px;
}

header nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

header nav ul li {
    display: inline;
    margin: 0 15px;
}

header nav ul li a {
    color: #e0e0e0;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s;
}

header nav ul li a:hover {
    color: #ff007f; /* Vibrant color on hover */
}

.profile-section {
    max-width: 600px;
    margin: 20px auto;
    padding: 20px;
    background: #1c1c1c;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
    text-align: center;
    animation: fadeInSection 1s ease-out;
}

.profile-pic {
    border-radius: 50%;
    width: 150px;
    height: 150px;
    object-fit: cover;
    margin-bottom: 20px;
    border: 4px solid #444; /* Subtle border color */
    transition: transform 0.3s;
}

.profile-pic:hover {
    transform: scale(1.1);
}

.edit-profile-photo {
    margin-top: 20px;
}

.edit-profile-photo button {
    background: #ff007f; /* Vibrant button color */
    color: #ffffff;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s, transform 0.3s;
}

.edit-profile-photo button:hover {
    background: #e6006f; /* Darker shade on hover */
    transform: scale(1.05);
}

.bio-section {
    margin-top: 20px;
}

.bio-section h3 {
    margin-bottom: 10px;
    font-size: 18px;
    color: #e0e0e0;
}

.bio-section p {
    margin: 10px 0;
    color: #b0b0b0;
    font-size: 16px;
}

.bio-section button {
    background: #ff007f; /* Vibrant button color */
    color: #ffffff;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s, transform 0.3s;
}

.bio-section button:hover {
    background: #e6006f; /* Darker shade on hover */
    transform: scale(1.05);
}

/* Modal Styles */
.modal, .popup {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.7); /* Darker overlay */
    animation: fadeInModal 0.3s ease-out;
}

.modal-content, .popup-content {
    background-color: #1c1c1c;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #333;
    width: 90%;
    max-width: 500px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.6);
    transform: scale(0.9);
    animation: scaleInModal 0.3s ease-out;
}

.modal-header, .modal-footer, .popup-header, .popup-footer {
    padding: 10px;
    background: #2c2c2c;
    border-bottom: 1px solid #333;
}

.modal-footer, .popup-footer {
    border-top: 1px solid #333;
    text-align: right;
}

.modal-header h2, .popup-header h2 {
    margin: 0;
    color: #e0e0e0;
}

.modal-footer button, .popup-footer button {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s, transform 0.3s;
}

.modal-footer .confirm, .popup-footer .confirm {
    background-color: #ff007f;
    color: #ffffff;
}

.modal-footer .confirm:hover, .popup-footer .confirm:hover {
    background-color: #e6006f;
    transform: scale(1.05);
}

.modal-footer .cancel, .popup-footer .cancel {
    background-color: #333;
    color: #e0e0e0;
}

.modal-footer .cancel:hover, .popup-footer .cancel:hover {
    background-color: #444;
    transform: scale(1.05);
}

@keyframes fadeInBody {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideInHeader {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes fadeInSection {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInModal {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes scaleInModal {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

</style>
</head>
<body>
    <header>
        <h1>Profile</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="profile-section">
        <?php if ($profile_picture): ?>
            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic">
        <?php else: ?>
            <img src="default-profile.png" alt="Profile Picture" class="profile-pic">
        <?php endif; ?>
        <div class="edit-profile-photo">
            <button id="change-profile-photo-btn">Change Profile Picture</button>
            <form id="file-upload-form" method="post" enctype="multipart/form-data" style="display: none;">
                <input type="file" id="file-upload" name="profile_picture" accept="image/*">
                <input type="submit" name="change_profile_picture" value="Upload">
            </form>
        </div>
        <div class="bio-section">
            <h3>Bio</h3>
            <p><?php echo htmlspecialchars($bio); ?></p>
            <button id="edit-bio-btn">Edit Bio</button>
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
                    <button type="submit" name="update_bio" class="confirm">Save</button>
                    <button type="button" class="cancel">Cancel</button>
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
