<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$query = "SELECT email, username, profile_picture, two_factor_enabled, password_hash FROM users WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user_info) {
    $current_email = $user_info['email'];
    $current_username = $user_info['username'];
    $current_profile_picture = $user_info['profile_picture'];
    $two_factor_enabled = $user_info['two_factor_enabled'];
    $hashed_password = $user_info['password_hash'];
} else {
    die("User not found.");
}
$stmt->closeCursor();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['profile_picture']['tmp_name'];
        $file_name = basename($_FILES['profile_picture']['name']);
        $upload_dir = 'profile_pics/'; // Ensure this matches other pages
        $file_path = $upload_dir . $file_name;

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['profile_picture']['type'], $allowed_types)) {
            if (move_uploaded_file($file_tmp_name, $file_path)) {
                // Update profile picture in database
                $query = "UPDATE users SET profile_picture = :profile_picture WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':profile_picture', $file_name, PDO::PARAM_STR); // Store only the file name
                $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    echo "Profile picture updated successfully.";
                    $current_profile_picture = $file_path; // Update current profile picture path
                } else {
                    echo "Error updating profile picture in database: " . $stmt->errorInfo()[2];
                }
                $stmt->closeCursor();
            } else {
                echo "Error moving uploaded file.";
            }
        } else {
            echo "Invalid file type.";
        }
    }

    // Update email
    if (!empty($_POST['email']) && $_POST['email'] != $current_email) {
        $new_email = $_POST['email'];
        $query = "UPDATE users SET email = :email WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $new_email, PDO::PARAM_STR);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            echo "Error updating email: " . $stmt->errorInfo()[2];
        }
        $stmt->closeCursor();
    }

    // Check if a new password is provided and if current password is correct
    if (!empty($_POST['password']) && !empty($_POST['current_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['password'];

        // Verify current password
        if (password_verify($current_password, $hashed_password)) {
            $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);

            $query = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':password_hash', $hashed_new_password, PDO::PARAM_STR);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo "Password updated successfully.";
            } else {
                echo "Error updating password: " . $stmt->errorInfo()[2];
            }
            $stmt->closeCursor();
        } else {
            echo "Current password is incorrect.";
        }
    }

    // Update two-factor authentication status
    $two_factor_enabled = isset($_POST['two_factor']) ? 1 : 0;
    $query = "UPDATE users SET two_factor_enabled = :two_factor WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':two_factor', $two_factor_enabled, PDO::PARAM_INT);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

    if (!$stmt->execute()) {
        echo "Error updating two-factor authentication status: " . $stmt->errorInfo()[2];
    }
    $stmt->closeCursor();

    // Handle account deletion
    if (isset($_POST['delete_account'])) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            session_unset();
            session_destroy();
            header("Location: login.php?message=Account deleted successfully.");
            exit();
        } else {
            echo "Error deleting account: " . $stmt->errorInfo()[2];
        }
        $stmt->closeCursor();
    }

    // Close the connection
    $pdo = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="settings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <h1>Settings</h1>
        <nav>
    <ul>
        <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
        <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

    </header>

    <section class="settings-section">
        <h2><i class="fas fa-cogs"></i> Account Settings</h2>

        <!-- Profile Picture Section -->
        <div class="profile-pic-container">
            <img id="profile-pic-preview" src="<?php echo htmlspecialchars($current_profile_picture ? 'profile_pics/' . $current_profile_picture : 'default-profile.png'); ?>" alt="Profile Picture" class="profile-pic">
            <div class="profile-pic-upload">
                <form id="profile-pic-form" action="settings.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                    <input type="submit" value="Upload New Picture">
                </form>
            </div>
        </div>

        <!-- Settings Form -->
        <form id="settings-form" action="settings.php" method="post">
            <label for="username"><i class="fas fa-user-tag"></i> Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($current_username); ?>"><br>
            <label for="current_email"><i class="fas fa-envelope"></i> Current Email:</label>
            <input type="text" id="current_email" name="current_email" value="<?php echo htmlspecialchars($current_email); ?>" readonly><br>
            <label for="email"><i class="fas fa-envelope-open-text"></i> New Email:</label>
            <input type="email" id="email" name="email"><br>
            <label for="current_password"><i class="fas fa-key"></i> Current Password:</label>
            <input type="password" id="current_password" name="current_password"><br>
            <label for="password"><i class="fas fa-lock"></i> New Password:</label>
            <input type="password" id="password" name="password"><br>
            <div id="password-strength" class="password-strength"></div>
            <label for="two_factor"><i class="fas fa-shield-alt"></i> Enable Two-Factor Authentication:</label>
            <input type="checkbox" id="two_factor" name="two_factor" <?php echo $two_factor_enabled ? 'checked' : ''; ?>><br>
            <input type="submit" value="Update Settings">
        </form>

        <!-- Notification Preferences -->
        <div class="notification-preferences">
            <h3><i class="fas fa-bell"></i> Notification Preferences</h3>
            <form action="update_notifications.php" method="post">
                <label for="email_notifications"><i class="fas fa-envelope"></i> Receive Email Notifications:</label>
                <input type="checkbox" id="email_notifications" name="email_notifications" checked><br>
                <input type="submit" value="Save Preferences">
            </form>
        </div>

        <!-- Delete Account -->
        <div class="delete-account">
            <h3><i class="fas fa-trash"></i> Delete Account</h3>
            <form action="settings.php" method="post" onsubmit="return confirmDeletion();">
                <input type="hidden" name="delete_account" value="1">
                <button type="submit"><i class="fas fa-trash-alt"></i> Delete My Account</button>
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
