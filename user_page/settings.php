<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$query = "SELECT email, username, profile_picture, two_factor_enabled, password_hash FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($current_email, $current_username, $current_profile_picture, $two_factor_enabled, $hashed_password);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update email
    if (!empty($_POST['email']) && $_POST['email'] != $current_email) {
        $new_email = $_POST['email'];
        $query = "UPDATE users SET email = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $new_email, $user_id);

        if (!$stmt->execute()) {
            echo "Error updating email: " . $stmt->error;
        }
        $stmt->close();
    }

    // Check if a new password is provided and if current password is correct
    if (!empty($_POST['password']) && !empty($_POST['current_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['password'];

        // Verify current password
        if (password_verify($current_password, $hashed_password)) {
            $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);

            $query = "UPDATE users SET password_hash = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $hashed_new_password, $user_id);

            if ($stmt->execute()) {
                echo "Password updated successfully.";
            } else {
                echo "Error updating password: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Current password is incorrect.";
        }
    }

    // Update two-factor authentication status
    $two_factor_enabled = isset($_POST['two_factor']) ? 1 : 0;
    $query = "UPDATE users SET two_factor_enabled = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $two_factor_enabled, $user_id);

    if (!$stmt->execute()) {
        echo "Error updating two-factor authentication status: " . $stmt->error;
    }
    $stmt->close();

    // Handle account deletion
    if (isset($_POST['delete_account'])) {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            session_unset();
            session_destroy();
            header("Location: login.php?message=Account deleted successfully.");
            exit();
        } else {
            echo "Error deleting account: " . $stmt->error;
        }
        $stmt->close();
    }

    // Close the connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="settings.css">
</head>
<body>
    <header>
        <h1>Settings</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <section class="settings-section">
        <h2>Account Settings</h2>

        <!-- Profile Picture Section -->
        <div class="profile-pic-container">
            <img id="profile-pic-preview" src="<?php echo htmlspecialchars($current_profile_picture); ?>" alt="Profile Picture" class="profile-pic">
            <div class="profile-pic-upload">
                <form id="profile-pic-form" action="update_profile_picture.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                    <input type="submit" value="Upload New Picture">
                </form>
            </div>
        </div>

        <!-- Settings Form -->
        <form id="settings-form" action="settings.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($current_username); ?>"><br>
            <label for="current_email">Current Email:</label>
            <input type="text" id="current_email" name="current_email" value="<?php echo htmlspecialchars($current_email); ?>" readonly><br>
            <label for="email">New Email:</label>
            <input type="email" id="email" name="email"><br>
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password"><br>
            <label for="password">New Password:</label>
            <input type="password" id="password" name="password"><br>
            <div id="password-strength" class="password-strength"></div>
            <label for="two_factor">Enable Two-Factor Authentication:</label>
            <input type="checkbox" id="two_factor" name="two_factor" <?php echo $two_factor_enabled ? 'checked' : ''; ?>><br>
            <input type="submit" value="Update Settings">
        </form>

        <!-- Notification Preferences -->
        <div class="notification-preferences">
            <h3>Notification Preferences</h3>
            <form action="update_notifications.php" method="post">
                <label for="email_notifications">Receive Email Notifications:</label>
                <input type="checkbox" id="email_notifications" name="email_notifications" checked><br>
                <input type="submit" value="Save Preferences">
            </form>
        </div>

        <!-- Delete Account -->
        <div class="delete-account">
            <h3>Delete Account</h3>
            <form action="settings.php" method="post" onsubmit="return confirmDeletion();">
                <input type="hidden" name="delete_account" value="1">
                <button type="submit">Delete My Account</button>
            </form>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Your Website. All rights reserved.</p>
    </footer>

    <script>
        document.getElementById('profile_picture').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-pic-preview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = document.getElementById('password-strength');
            let strengthClass = '';
            if (password.length >= 12 && /[a-z]/.test(password) && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[\W_]/.test(password)) {
                strengthClass = 'strong';
            } else if (password.length >= 8 && /[a-z]/.test(password) && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
                strengthClass = 'medium';
            } else {
                strengthClass = 'weak';
            }
            strength.className = `password-strength ${strengthClass}`;
            strength.textContent = strengthClass.charAt(0).toUpperCase() + strengthClass.slice(1) + ' Password';
        });

        function confirmDeletion() {
            return confirm("Are you sure you want to delete your account? This action cannot be undone.");
        }
    </script>
</body>
</html>
