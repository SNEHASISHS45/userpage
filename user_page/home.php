<?php
session_start();
include 'db_connect.php'; // Ensure this file creates the $pdo variable

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; // Default to 'Guest' if not set

// Fetch user's profile picture
$query = "SELECT profile_picture FROM users WHERE id = :user_id";
$stmt = $pdo->prepare($query);

if ($stmt === false) {
    die("Prepare failed: " . $pdo->errorInfo()[2]);
}

// Bind parameters
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$profile_picture = $stmt->fetchColumn(); // Fetch a single column value
$stmt->closeCursor();
$pdo = null; // Close the PDO connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Home</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <header>
        <nav>
            <div class="nav-container">
                <div class="profile-info">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic">
                    <span class="username"><?php echo htmlspecialchars($username); ?></span>
                </div>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
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
    
    <footer>
        <p>&copy; 2024 Your Website. All rights reserved.</p>
    </footer>
</body>
</html>
