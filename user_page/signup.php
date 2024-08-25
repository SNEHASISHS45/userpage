<?php
include 'db_connect.php';
session_start();

// Initialize error message
$error_message = '';

if (isset($_POST['username'], $_POST['password'], $_POST['email'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Check if email already exists
    $check_email_query = "SELECT id FROM users WHERE email = ?";
    $check_email_stmt = $conn->prepare($check_email_query);

    if ($check_email_stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $check_email_stmt->bind_param("s", $email);
    $check_email_stmt->execute();
    $check_email_stmt->store_result();

    if ($check_email_stmt->num_rows > 0) {
        $error_message = "Error: This email address is already registered.";
        $check_email_stmt->close();
        $conn->close();
    } else {
        $check_email_stmt->close();

        // Prepare and execute the SQL statement
        $query = "INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }

        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bind_param("sss", $username, $password_hash, $email);
        $success = $stmt->execute();

        if ($success) {
            header("Location: login.php");
            exit();
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="signup.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="form-container">
        <h1>Sign Up</h1>
        <form action="signup.php" method="post">
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

            <div class="input-container">
                <i class="fas fa-envelope icon"></i>
                <input type="email" id="email" name="email" required placeholder=" ">
                <label for="email">Email</label>
            </div>

            <input type="submit" value="Sign Up">

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
        </form>
        <div class="form-links">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
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
