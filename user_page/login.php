<?php
session_start();
include 'db_connect.php';

// Initialize error_message and logout_message variables
$error_message = '';
$logout_message = '';

// Check if the user has been logged out
if (isset($_GET['message']) && $_GET['message'] == 'logged_out') {
    $logout_message = 'You have been logged out successfully. Please log in again.';
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if form fields are set
    if (isset($_POST['username'], $_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Prepare and execute SQL statement
        $query = "SELECT id, username, password_hash FROM users WHERE username = :username";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            die("Prepare failed: " . $conn->errorInfo()[2]);
        }

        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $user = $stmt->fetch();
        if ($user) {
            $id = $user['id'];
            $username = $user['username'];
            $password_hash = $user['password_hash'];

            if (password_verify($password, $password_hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                header("Location: home.php");
                exit();
            } else {
                $error_message = "Invalid username or password.";
            }
        } else {
            $error_message = "Invalid username or password.";
        }

        $stmt->closeCursor();
    } else {
        $error_message = "Username and password are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="background-animation"></div>
    <div class="form-container">
        <h1>Login</h1>
        <form action="login.php" method="post">
            <div class="input-container">
                <i class="fas fa-user icon"></i>
                <input type="text" id="username" name="username" required placeholder=" ">
                <label for="username">Username</label>
            </div>

            <div class="input-container">
                <i class="fas fa-lock icon"></i>
                <input type="password" id="password" name="password" required placeholder=" ">
                <label for="password">Password</label>
            </div>

            <input type="submit" value="Login">

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($logout_message)): ?>
                <div class="logout-message" id="logout-message"><?php echo htmlspecialchars($logout_message); ?></div>
            <?php endif; ?>
        </form>
        <div class="form-links">
           <p>Don't have an account yet? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>

    <script>
    window.addEventListener('DOMContentLoaded', (event) => {
        // Hide the logout message after 5 seconds
        var logoutMessage = document.getElementById('logout-message');
        if (logoutMessage) {
            setTimeout(function() {
                logoutMessage.style.opacity = 0;
                setTimeout(function() {
                    logoutMessage.style.display = 'none';
                }, 500); 
            }, 5000); 
        }

        // Hide the error message after 3 seconds
        var errorMessage = document.querySelector('.error-message');
        if (errorMessage) {
            setTimeout(function() {
                errorMessage.style.opacity = 0;
                setTimeout(function() {
                    errorMessage.style.display = 'none';
                }, 500); 
            }, 3000); 
        }
    });
    </script>
</body>
</html>
