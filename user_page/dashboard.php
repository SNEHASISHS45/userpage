<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$query = "SELECT username, profile_picture, bio FROM users WHERE id = :id";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Prepare failed: " . $conn->errorInfo()[2]);
}

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
$stmt->closeCursor(); // Close the cursor to allow another statement to be executed

// Fetch recent activities
$recent_activities_query = "SELECT activity, timestamp FROM activities WHERE user_id = :user_id ORDER BY timestamp DESC LIMIT 5";
$activities_stmt = $conn->prepare($recent_activities_query);

if ($activities_stmt === false) {
    die("Prepare failed: " . $conn->errorInfo()[2]);
}

$activities_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$activities_stmt->execute();
$activities = $activities_stmt->fetchAll(PDO::FETCH_ASSOC);

$activities_stmt->closeCursor(); // Close the cursor
$conn = null; // Close PDO connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <h1>Dashboard</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <section class="dashboard-section">
        <div class="profile-section">
            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic">
            <h2><?php echo htmlspecialchars($username); ?></h2>
            <p><?php echo htmlspecialchars($bio); ?></p>
        </div>

        <div class="dashboard-stats">
            <h3><i class="fas fa-chart-bar"></i> Dashboard Stats</h3>
            <p>Welcome back, <?php echo htmlspecialchars($username); ?>! Here are your latest statistics:</p>
            <!-- Add your dashboard stats here -->
        </div>

        <div class="recent-activities">
            <h3><i class="fas fa-calendar-check"></i> Recent Activities</h3>
            <ul>
                <?php foreach ($activities as $activity): ?>
                    <li><?php echo htmlspecialchars($activity['activity']); ?> - <small><?php echo htmlspecialchars($activity['timestamp']); ?></small></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="notifications">
            <h3><i class="fas fa-bell"></i> Notifications</h3>
            <ul>
                <li><a href="#"><i class="fas fa-comment-dots"></i> New comment on your post</a></li>
                <li><a href="#"><i class="fas fa-user-plus"></i> You have a new follower</a></li>
                <li><a href="#"><i class="fas fa-download"></i> System update available</a></li>
            </ul>
        </div>

        <div class="quick-links">
            <h3><i class="fas fa-link"></i> Quick Links</h3>
            <ul>
                <li><a href="profile.php"><i class="fas fa-user-circle"></i> View Profile</a></li>
                <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
                <li><a href="profile.php"><i class="fas fa-edit"></i> Edit Profile</a></li>
            </ul>
        </div>

        <div class="charts">
            <h3><i class="fas fa-chart-line"></i> Data Visualization</h3>
            <canvas id="userChart" width="400" height="200"></canvas>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Your Website. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('userChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
                datasets: [{
                    label: 'User Data',
                    data: [12, 19, 3, 5, 2, 3, 7],
                    backgroundColor: 'rgba(75, 192, 192, 0.3)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(75, 192, 192, 1)'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
