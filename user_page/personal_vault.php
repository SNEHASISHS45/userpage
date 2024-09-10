<?php
session_start();
include 'db_connect.php';


// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection variables
$host = 'localhost';
$dbname = 'user_page';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Could not connect to the database.");
}

// Update activity when loading the gallery
updateActivity($pdo, $_SESSION['user_id'], 'vault');

// Update user activity
function updateActivity($pdo, $user_id, $activity_type) {
    $now = date('Y-m-d H:i:s');
    $query = "INSERT INTO user_activity (user_id, activity_type, last_opened)
              VALUES (:user_id, :activity_type, :last_opened)
              ON DUPLICATE KEY UPDATE last_opened = VALUES(last_opened)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':activity_type', $activity_type, PDO::PARAM_STR);
    $stmt->bindParam(':last_opened', $now, PDO::PARAM_STR);
    $stmt->execute();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';

// Initialize search and category variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_upload'])) {
    // Upload handling code
    // ... [Same as your existing code]
}

// Build the query with search and category filters
$query = "SELECT * FROM vault_items WHERE user_id = :user_id";
$params = [':user_id' => $user_id];

if ($search) {
    $query .= " AND (title LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($category) {
    $query .= " AND category = :category";
    $params[':category'] = $category;
}

$query .= " ORDER BY created_at DESC"; // Optional: sort by creation date

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$vault_items = $stmt->fetchAll();
$stmt->closeCursor();

// Update activity when loading the contacts page
updateActivity($pdo, $user_id, 'personal_vault');

// Close PDO connection
$pdo = null;
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Vault</title>
    <link rel="stylesheet" href="vault.scss"> <!-- Link to the compiled CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<header>
        <nav>
            <h1>Personal vault</h1>
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

    <main>
        <section class="vault-section">
          

            <!-- Upload Form -->
            <div class="upload-form">
                <h2>Upload New Item</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="title" placeholder="Title" required>
                    <textarea name="description" placeholder="Description" required></textarea>
                    <input type="file" name="file_upload" required>
                    <input type="submit" value="Upload">
                </form>
            </div>

            <!-- Search and Category Filter -->
            <div class="filters">
                <form method="GET" action="personal_vault.php">
                    <input type="text" name="search" placeholder="Search items" value="<?php echo htmlspecialchars($search); ?>">
                    <select name="category">
                        <option value="">All Categories</option>
                        <option value="Work" <?php if ($category === 'Work') echo 'selected'; ?>>Work</option>
                        <option value="Personal" <?php if ($category === 'Personal') echo 'selected'; ?>>Personal</option>
                        <!-- Add more categories as needed -->
                    </select>
                    <input type="submit" value="Filter">
                </form>
            </div>

            <!-- Vault Items List -->
            <div class="vault-items">
                <?php if (empty($vault_items)): ?>
                    <p>No items found in your vault.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($vault_items as $item): ?>
                            <li>
                                <div class="item">
                                    <h2><?php echo htmlspecialchars($item['title']); ?></h2>
                                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                                    <?php if (!empty($item['file_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($item['file_path']); ?>" class="btn-download">Download</a>
                                    <?php endif; ?>
                                    <a href="view_item.php?id=<?php echo $item['id']; ?>" class="btn-view">View</a>
                                    <a href="delete_item.php?id=<?php echo $item['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        document.getElementById('nav-toggle').addEventListener('click', function () {
            const menu = document.getElementById('nav-menu');
            menu.classList.toggle('active');
        });
    </script>
</body>

</html>
