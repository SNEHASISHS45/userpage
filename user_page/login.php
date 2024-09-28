<?php
session_start();
include 'db_connect.php';

// Initialize error_message variable
$error_message = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_username'], $_POST['login_password'])) {
    $username = $_POST['login_username'];
    $password = $_POST['login_password'];

    $query = "SELECT id, username, password_hash FROM users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: home.php");
        exit();
    } else {
        $error_message = "Invalid username or password.";
    }
}

// Process signup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup_username'], $_POST['signup_password'], $_POST['signup_email'])) {
    $username = $_POST['signup_username'];
    $password = $_POST['signup_password'];
    $email = $_POST['signup_email'];

    $check_email_query = "SELECT id FROM users WHERE email = :email";
    $check_email_stmt = $pdo->prepare($check_email_query);
    $check_email_stmt->bindParam(':email', $email);
    $check_email_stmt->execute();

    if ($check_email_stmt->rowCount() > 0) {
        $error_message = "Error: This email address is already registered.";
    } else {
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Sign Up</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.21/build/spline-viewer.js" async></script>
</head>
<body>
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.27/build/spline-viewer.js"></script>
<spline-viewer loading-anim-type="spinner-small-light" url="https://prod.spline.design/8TpOImH7QKlXoUTY/scene.splinecode"></spline-viewer>

    <div class="container">
        <div class="form-wrapper">
            <div class="form-container" id="signup-form">
                <h1>Sign Up</h1>
                <form action="login.php" method="post">
                    <div class="input-container">
                        <i class="fas fa-user icon"></i>
                        <input type="text" placeholder="Username" name="signup_username" required>
                    </div>
                    <div class="input-container">
                        <i class="fas fa-envelope icon"></i>
                        <input type="email" placeholder="Email" name="signup_email" required>
                    </div>
                    <div class="input-container">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" placeholder="Password" name="signup_password" id="signup-password" required>
                        <i id="eye" class="fas fa-eye password-toggle" onclick="togglePassword('signup-password', this)"></i>
                    </div>
                    <button type="submit">Sign Up</button>
                    <div class="form-links">
                        <p>Already have an account? <a href="#" onclick="showLogin()">Login</a></p>
                    </div>
                    <?php if ($error_message): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                </form>
            </div>

            <div class="form-container" id="login-form">
                <h1>Login</h1>
                <form action="login.php" method="post">
                    <div class="input-container">
                        <i class="fas fa-user icon"></i>
                        <input type="text" placeholder="Username" name="login_username" required>
                    </div>
                    <div class="input-container">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" placeholder="Password" name="login_password" id="login-password" required>
                        <i id="eye" class="fas fa-eye password-toggle" onclick="togglePassword('login-password', this)"></i>
                    </div>
                    <button type="submit" value="Sign Up">Login</button>
                    <div class="form-links">
                        <p>Don't have an account? <a href="#" onclick="showSignup()">Register</a></p>
                    </div>
                    <?php if ($error_message): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <script>
    function showSignup() {
        document.getElementById('signup-form').style.transform = 'translateX(0)';
        document.getElementById('login-form').style.transform = 'translateX(100%)';
    }

    function showLogin() {
        document.getElementById('signup-form').style.transform = 'translateX(-100%)';
        document.getElementById('login-form').style.transform = 'translateX(0)';
    }

    function togglePassword(inputId, eyeIcon) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            eyeIcon.classList.remove("fa-eye-slash");
            eyeIcon.classList.add("fa-eye");
        }
    }

    // Initially show the login form
    showLogin();
    </script>
</body>
</html>
