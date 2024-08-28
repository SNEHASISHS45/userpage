<?php
include 'db_connect.php';
session_start();

// Initialize error message
$error_message = '';

if (isset($_POST['username'], $_POST['password'], $_POST['email'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    try {
        // Check if email already exists
        $check_email_query = "SELECT id FROM users WHERE email = :email";
        $check_email_stmt = $pdo->prepare($check_email_query);
        $check_email_stmt->bindParam(':email', $email);
        $check_email_stmt->execute();

        if ($check_email_stmt->rowCount() > 0) {
            $error_message = "Error: This email address is already registered.";
        } else {
            // Prepare and execute the SQL statement
            $query = "INSERT INTO users (username, password_hash, email) VALUES (:username, :password_hash, :email)";
            $stmt = $pdo->prepare($query);

            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':email', $email);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Error: Could not execute the query.";
            }
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
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
