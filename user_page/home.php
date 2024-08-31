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
    <title>User Home</title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="nav-container">
                <div class="profile-info">
                    <img src="<?php echo htmlspecialchars($profile_picture_path); ?>" alt="Profile Picture" class="profile-pic">
                    <span class="username"><?php echo htmlspecialchars($username); ?></span>
                </div>
                <div class="nav-toggle" id="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
                <ul id="nav-menu" class="nav-menu">
                    <li><a href="dashboard.php"><i class="fa-solid fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                    <li><a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a></li>
                    <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

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
    document.getElementById('nav-toggle').addEventListener('click', function() {
        const menu = document.getElementById('nav-menu');
        menu.classList.toggle('active');
    });
    </script>

    <footer>
        <p>&copy; 2024 Your Website. All rights reserved.</p>
    </footer>
</body>
</html>
