<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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

// Fetch recent activities
$recent_activities_query = "SELECT activity, timestamp FROM activities WHERE user_id = ? ORDER BY timestamp DESC LIMIT 5";
$activities_stmt = $conn->prepare($recent_activities_query);

if ($activities_stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$activities_stmt->bind_param("i", $user_id);
$activities_stmt->execute();
$activities_result = $activities_stmt->get_result();

$activities = [];
while ($activity = $activities_result->fetch_assoc()) {
    $activities[] = $activity;
}
$activities_stmt->close();
$conn->close();
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
