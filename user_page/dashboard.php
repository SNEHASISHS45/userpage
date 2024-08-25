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
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1c1c1c, #333);
            color: #e0e0e0;
            padding: 20px;
            margin: 0;
            animation: fadeInBody 1s ease-out;
        }

        header {
            background: #1c1c1c;
            color: #e0e0e0;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #444;
            animation: slideInHeader 1s ease-out;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        header nav ul {
            list-style: none;
            padding: 0;
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

        .dashboard-section {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #1c1c1c;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
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

        .dashboard-stats, .recent-activities, .notifications, .quick-links, .charts {
            margin-bottom: 20px;
            color: #e0e0e0;
        }

        .dashboard-stats h3, .recent-activities h3, .notifications h3, .quick-links h3, .charts h3 {
            color: #ff007f; /* Vibrant color for section titles */
        }

        .dashboard-stats p, .recent-activities ul, .notifications ul, .quick-links ul {
            background: #2c2c2c;
            border-radius: 8px;
            padding: 10px;
        }

        .dashboard-stats p {
            margin: 10px 0;
        }

        .recent-activities ul, .notifications ul, .quick-links ul {
            list-style: none;
            padding: 0;
        }

        .recent-activities li, .notifications li, .quick-links li {
            margin-bottom: 10px;
        }

        .recent-activities li a, .notifications li a, .quick-links li a {
            color: #e0e0e0;
            text-decoration: none;
            transition: color 0.3s;
        }

        .recent-activities li a:hover, .notifications li a:hover, .quick-links li a:hover {
            color: #ff007f; /* Vibrant color on hover */
        }

        footer {
            text-align: center;
            padding: 10px;
            background: #1c1c1c;
            color: #e0e0e0;
            border-top: 1px solid #444;
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
    </style>
</head>
<body>
    <header>
        <h1>Dashboard</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <section class="dashboard-section">
        <div class="dashboard-welcome">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <img src="<?php echo htmlspecialchars($profile_picture ? $profile_picture : 'default_profile_picture.jpg'); ?>" alt="Profile Picture" class="profile-pic">
            <p><?php echo htmlspecialchars($bio); ?></p>
        </div>

        <div class="dashboard-stats">
            <h3>Statistics</h3>
            <p><strong>Total Posts:</strong> <?php // Example value ?></p>
            <p><strong>Followers:</strong> <?php // Example value ?></p>
            <p><strong>Following:</strong> <?php // Example value ?></p>
        </div>

        <div class="recent-activities">
            <h3>Recent Activities</h3>
            <ul>
                <?php foreach ($activities as $activity): ?>
                    <li><?php echo htmlspecialchars($activity['activity']); ?> - <?php echo htmlspecialchars($activity['timestamp']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="notifications">
            <h3>Notifications</h3>
            <ul>
                <li><a href="#">New comment on your post</a></li>
                <li><a href="#">You have a new follower</a></li>
                <li><a href="#">System update available</a></li>
            </ul>
        </div>

        <div class="quick-links">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="profile.php">View Profile</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="profile.php">Edit Profile</a></li>
            </ul>
        </div>

        <div class="charts">
            <h3>Data Visualization</h3>
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
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
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
