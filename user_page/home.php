<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user profile information
$query = "SELECT profile_picture FROM users WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$profile_picture = $stmt->fetchColumn(); // Fetch a single column value
$stmt->closeCursor(); // Close the cursor to allow another statement to be executed
$pdo = null; // Close PDO connection

// Define the path to the profile pictures directory
$profile_pics_dir = 'profile_pics/';
$profile_picture_path = $profile_pics_dir . $profile_picture;

// Use default picture if the profile picture does not exist
if (!file_exists($profile_picture_path) || empty($profile_picture)) {
    $profile_picture_path = 'profile_pics/default-profile.png'; // Default profile picture path
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; // Default to 'Guest' if not set
?>






<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome, <?php echo htmlspecialchars($username); ?>!</title>
    <link rel="stylesheet" href="home.scss"> <!-- Link to the compiled CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.21/build/spline-viewer.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <style>
        /* Styles for positioning the 3D background */
        spline-viewer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1; /* Ensure it stays behind other content */
        }
        
    </style>
</head>

<body>
    <header>
        <nav>
            <div class="nav-container">
                <!-- Brand Logo -->
                <div class="brand-logo">
                <h1 style="color: white;">SNEHASISH</h1><!-- Replace with your logo -->
                </div>

                <!-- Navigation Menu -->
                <ul id="nav-menu" class="nav-menu">
                    <li><a href="dashboard.php"><i class="fa-solid fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                    <li><a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a></li>
                    <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
                </ul>

                <!-- Profile Info -->
                <div class="profile-info">
                    <img src="<?php echo htmlspecialchars($profile_picture_path); ?>" alt="Profile Picture" class="profile-pic">
                    <span class="username"><?php echo htmlspecialchars($username); ?></span>
                </div>

                <!-- Hamburger Menu -->
                <div class="nav-toggle" id="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>

    
<div class="sp">
    <!-- Spline 3D Viewer -->
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.21/build/spline-viewer.js" async></script>
<spline-viewer loading-anim-type="spinner-small-light" url="https://prod.spline.design/8TpOImH7QKlXoUTY/scene.splinecode"><img src="data:image/png;base64,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" alt="Spline preview" style="width: 100%; height: 100%;"/></spline-viewer>
    </div>

    <section class="home-section">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <div class="home-links">
            <a href="gallery.php" class="home-link">Gallery</a>
            <a href="documents.php" class="home-link">Documents</a>
            <a href="contacts.php" class="home-link">Contacts</a>
            <a href="personal_vault.php" class="home-link">Personal Vault</a>
        </div>
    </section>
    <script>
        document.getElementById('nav-toggle').addEventListener('click', function () {
            const menu = document.getElementById('nav-menu');
            menu.classList.toggle('active');
        });
    </script>
</body>

</html>
