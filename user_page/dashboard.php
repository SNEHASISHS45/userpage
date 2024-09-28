<?php
// Debugging code
error_log("Debugging: Reached the top of the script");

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection and start session
include 'db_connect.php'; // Ensure this path is correct
session_start();

if (!isset($_SESSION['username'])) {
    error_log("Debugging: No username in session, redirecting to login.");
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Ensure $pdo is initialized
if (!isset($pdo)) {
    error_log("Debugging: PDO is not initialized.");
    die("Database connection not established.");
}

// Get user_id from username
function getUserId($pdo, $username) {
    try {
        $query = "SELECT id FROM users WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $user ? $user['id'] : null;
    } catch (PDOException $e) {
        error_log("Error fetching user ID: " . $e->getMessage());
        return null;
    }
}

$user_id = getUserId($pdo, $username);

if (!$user_id) {
    error_log("Debugging: User ID not found for username $username.");
    echo "User ID not found.";
    exit();
}

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

// Update activity when loading the dashboard
updateActivity($pdo, $user_id, 'dashboard');

// Initialize variables
$username = $profile_picture = $bio = '';
$activity_counts = [
    'gallery' => 0,
    'contacts' => 0,
    'vault' => 0,
    'documents' => 0
];
$activity_last_opened = [
    'gallery' => 'Never',
    'contacts' => 'Never',
    'vault' => 'Never',
    'documents' => 'Never'
];

// Fetch user information
try {
    $query = "SELECT username, profile_picture, bio FROM users WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_info) {
        $username = htmlspecialchars($user_info['username'] ?? '');
        $profile_picture = htmlspecialchars($user_info['profile_picture'] ?? '');
        $bio = htmlspecialchars($user_info['bio'] ?? '');
    } else {
        error_log("Debugging: User not found with ID $user_id.");
        echo "User not found.";
        exit();
    }
    $stmt->closeCursor();
} catch (PDOException $e) {
    error_log("Error fetching user information: " . $e->getMessage());
    echo "Error fetching user information. Please try again later.";
    exit();
}

// Fetch activity data
try {
    $activity_query = "SELECT activity_type, COUNT(*) as count, MAX(last_opened) as last_opened FROM user_activity WHERE user_id = :user_id GROUP BY activity_type";
    $activity_stmt = $pdo->prepare($activity_query);
    $activity_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $activity_stmt->execute();

    while ($row = $activity_stmt->fetch(PDO::FETCH_ASSOC)) {
        $type = $row['activity_type'];
        if (array_key_exists($type, $activity_counts)) {
            $activity_counts[$type] = $row['count'];
            $activity_last_opened[$type] = $row['last_opened'] ? date('Y-m-d H:i:s', strtotime($row['last_opened'])) : 'Never';
        }
    }
    $activity_stmt->closeCursor();
} catch (PDOException $e) {
    error_log("Error fetching activity data: " . $e->getMessage());
    echo "Error fetching activity data.";
}

// Define the profile picture directory
$profile_pics_dir = 'profile_pics/';
$profile_picture_path = $profile_pics_dir . $profile_picture;

// Set default image if profile picture is not available
if (!file_exists($profile_picture_path)) {
    $profile_picture_path = 'profile_pics/default_profile_pic.jpg';
}

// Debugging code
error_log("Debugging: Profile picture path is " . $profile_picture_path);

// Function to fetch user storage data
function fetchUserStorageData($username, $pdo) {
    try {
        $query = "SELECT gallery_storage, contacts_storage, vault_storage, documents_storage, total_storage, max_storage 
                  FROM user_storage WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'gallery_storage' => 0,
            'contacts_storage' => 0,
            'vault_storage' => 0,
            'documents_storage' => 0,
            'total_storage' => 0,
            'max_storage' => 1024 // 1 GB in MB
        ];
    } catch (PDOException $e) {
        error_log("Error fetching user storage data: " . $e->getMessage());
        return [
            'gallery_storage' => 0,
            'contacts_storage' => 0,
            'vault_storage' => 0,
            'documents_storage' => 0,
            'total_storage' => 0,
            'max_storage' => 1024 // 1 GB in MB
        ];
    }
}

// Fetch user storage data
$storage_data = fetchUserStorageData($username, $pdo);

// Individual page storage
$storage_in_mb = [
    'gallery' => $storage_data['gallery_storage'] ?? 0,
    'contacts' => $storage_data['contacts_storage'] ?? 0,
    'vault' => $storage_data['vault_storage'] ?? 0,
    'documents' => $storage_data['documents_storage'] ?? 0,
];

// Calculate total and remaining storage
$total_storage_mb = $storage_data['total_storage'] ?? 0;
$max_storage_mb = $storage_data['max_storage'] ?? 1024; // Default to 1GB in MB
$storage_remaining_mb = $max_storage_mb - $total_storage_mb;

// Fetch user activity data
$activity_counts = fetchUserActivityCounts($username, $pdo); // Ensure this function is defined
$activity_last_opened = fetchUserActivityLastOpened($username, $pdo); // Ensure this function is defined

// Define fetchUserActivityCounts function
function fetchUserActivityCounts($username, $pdo) {
    try {
        $query = "SELECT activity_type, COUNT(*) as count FROM user_activity WHERE username = :username GROUP BY activity_type";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        $activity_counts = [
            'gallery' => 0,
            'contacts' => 0,
            'vault' => 0,
            'documents' => 0
        ];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $activity_counts[$row['activity_type']] = $row['count'];
        }

        return $activity_counts;
    } catch (PDOException $e) {
        error_log("Error fetching user activity counts: " . $e->getMessage());
        return [
            'gallery' => 0,
            'contacts' => 0,
            'vault' => 0,
            'documents' => 0
        ];
    }
}

// Define fetchUserActivityLastOpened function
function fetchUserActivityLastOpened($username, $pdo) {
    try {
        $query = "SELECT activity_type, MAX(last_opened) as last_opened FROM user_activity WHERE username = :username GROUP BY activity_type";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        $activity_last_opened = [
            'gallery' => 'Never',
            'contacts' => 'Never',
            'vault' => 'Never',
            'documents' => 'Never'
        ];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $activity_last_opened[$row['activity_type']] = $row['last_opened'] ? date('Y-m-d H:i:s', strtotime($row['last_opened'])) : 'Never';
        }

        return $activity_last_opened;
    } catch (PDOException $e) {
        error_log("Error fetching user activity last opened: " . $e->getMessage());
        return [
            'gallery' => 'Never',
            'contacts' => 'Never',
            'vault' => 'Never',
            'documents' => 'Never'
        ];
    }
}
?>









<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.scss">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Positioning for 3D background */
        spline-viewer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        /* Styles for draggable and resizable user activity window */
        .user-activity {
            position: absolute;
            top: 100px; /* Adjust as needed */
            left: 100px; /* Adjust as needed */
            background: transparent; /* Glass effect */
            border: 1px solid #ccc;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            cursor: move; /* Indicate draggable */
            resize: both; /* Allows for resizing */
            overflow: auto; /* Ensure content is scrollable when resized */
        }

        /* Styles for resize handles */
        .resize-handle {
            position: absolute;
            width: 10px;
            height: 10px;
            background: rgba(0, 0, 0, 0.5);
            cursor: se-resize; /* Southeast resize cursor */
        }

        .resize-handle.bottom-right {
            bottom: 0;
            right: 0;
        }

        .storage-status {
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        /* Progress bar styles */
        .progress-container {
            margin-top: 10px;
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            height: 20px;
        }

        .progress-bar {
            background-color: #4caf50;
            height: 100%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
<header>
    <nav>
        <h1>Dashboard</h1>
        <div class="nav-container">
            <ul id="nav-menu" class="nav-menu">
                <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="dashboard.php"><i class="fa-solid fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                <li><a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <div class="profile-info">
                <img src="<?php echo $profile_picture_path; ?>" alt="Profile Picture" class="profile-pic">
                <span class="username"><?php echo $username; ?></span>
            </div>
        </div>
    </nav>
</header>

<div class="sp">
<script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.27/build/spline-viewer.js"></script>
<spline-viewer url="https://prod.spline.design/Ylco7b1CsvLH5v89/scene.splinecode"></spline-viewer>
</div>

<div class="user-activity floating-window">
    <div class="header">
        <h3 class="move"><i class="fas fa-tachometer-alt"></i> User Activity</h3>
    </div>
    <ul>
        <li>Gallery: <span class="count"><?php echo isset($activity_counts['gallery']) ? $activity_counts['gallery'] : 0; ?></span> times - Last opened: <span class="last-opened"><?php echo isset($activity_last_opened['gallery']) ? $activity_last_opened['gallery'] : 'Never'; ?></span></li>
        <li>Contacts: <span class="count"><?php echo isset($activity_counts['contacts']) ? $activity_counts['contacts'] : 0; ?></span> times - Last opened: <span class="last-opened"><?php echo isset($activity_last_opened['contacts']) ? $activity_last_opened['contacts'] : 'Never'; ?></span></li>
        <li>Personal Vault: <span class="count"><?php echo isset($activity_counts['vault']) ? $activity_counts['vault'] : 0; ?></span> times - Last opened: <span class="last-opened"><?php echo isset($activity_last_opened['vault']) ? $activity_last_opened['vault'] : 'Never'; ?></span></li>
        <li>Documents: <span class="count"><?php echo isset($activity_counts['documents']) ? $activity_counts['documents'] : 0; ?></span> times - Last opened: <span class="last-opened"><?php echo isset($activity_last_opened['documents']) ? $activity_last_opened['documents'] : 'Never'; ?></span></li>
    </ul>
    <div class="resize-handle bottom-right"></div>
</div>

<div class="storage-status">
    <h3>Storage Tracker</h3>
    <ul>
        <li>Gallery: <?php echo $storage_in_mb['gallery'] ?? 0; ?> MB <button onclick="clearStorage('gallery')">Clear</button></li>
        <li>Contacts: <?php echo $storage_in_mb['contacts'] ?? 0; ?> MB <button onclick="clearStorage('contacts')">Clear</button></li>
        <li>Personal Vault: <?php echo $storage_in_mb['vault'] ?? 0; ?> MB <button onclick="clearStorage('vault')">Clear</button></li>
        <li>Documents: <?php echo $storage_in_mb['documents'] ?? 0; ?> MB <button onclick="clearStorage('documents')">Clear</button></li>
    </ul>
    <div class="progress-container">
        <label>Total Storage Used: <?php echo round($total_storage_mb, 2); ?> MB / 1024 MB</label>
        <div class="progress-bar">
            <div class="progress" style="width: <?php echo ($total_storage_mb / $max_storage_mb) * 100; ?>%;"></div>
        </div>
        <p>Remaining Storage: <?php echo round($storage_remaining_mb, 2); ?> MB</p>
    </div>
</div>




<script>
    document.addEventListener('DOMContentLoaded', () => {
        const userActivity = document.querySelector('.user-activity');
        const header = document.querySelector('.header');
        const handle = document.querySelector('.resize-handle');

        let isDragging = false;
        let startX, startY, startLeft, startTop;

        header.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            startLeft = parseInt(window.getComputedStyle(userActivity).left, 10);
            startTop = parseInt(window.getComputedStyle(userActivity).top, 10);

            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', stopDrag);
        });

        function drag(e) {
            if (isDragging) {
                const newLeft = startLeft + (e.clientX - startX);
                const newTop = startTop + (e.clientY - startY);
                userActivity.style.left = `${newLeft}px`;
                userActivity.style.top = `${newTop}px`;
            }
        }

        function stopDrag() {
            isDragging = false;
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', stopDrag);
        }

        // Resize functionality
        let isResizing = false;

        handle.addEventListener('mousedown', (e) => {
            isResizing = true;
            document.addEventListener('mousemove', resize);
            document.addEventListener('mouseup', stopResize);
        });

        function resize(e) {
            if (isResizing) {
                const newWidth = e.clientX - userActivity.getBoundingClientRect().left;
                const newHeight = e.clientY - userActivity.getBoundingClientRect().top;
                userActivity.style.width = `${newWidth}px`;
                userActivity.style.height = `${newHeight}px`;
            }
        }

        function stopResize() {
            isResizing = false;
            document.removeEventListener('mousemove', resize);
            document.removeEventListener('mouseup', stopResize);
        }
    });
</script>

</body>
</html>
