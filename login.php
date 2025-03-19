<?php
session_start();
require 'config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    // Handle Registration
    if ($action == "register") {
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        // Password validation
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            echo "Password must be at least 8 characters long and include uppercase, lowercase, a number, and a special character.";
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check if the username already exists
        $query = "SELECT id FROM users WHERE username = $1";
        $result = pg_query_params($conn, $query, [$username]);

        if (pg_num_rows($result) > 0) {
            echo "Username already taken.";
            exit();
        }

        // Insert new user
        $query = "INSERT INTO users (username, email, password) VALUES ($1, $2, $3)";
        $result = pg_query_params($conn, $query, [$username, $email, $hashed_password]);

        if ($result) {
            echo "Registration successful!";
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error: Could not register.";
        }
    }

    // Handle Login with Username
    if ($action == "login") {
        $username = trim($_POST["username"] ?? '');
        $password = trim($_POST["password"] ?? '');

        $query = "SELECT id, username, password, profile_picture FROM users WHERE username = $1";
        $result = pg_query_params($conn, $query, [$username]);

        if (pg_num_rows($result) == 0) {
            echo "Username not found.";
            exit();
        }

        $user = pg_fetch_assoc($result);

        // Verify Password
        if (password_verify($password, $user['password'])) {
            $_SESSION["user_id"] = $user['id'];
            $_SESSION["username"] = $user['username'];
            $_SESSION["profile_picture"] = $user['profile_picture'];

            // Set a cookie to remember the user
            $token = bin2hex(random_bytes(16));
            setcookie("login_token", $token, time() + (86400 * 30), "/"); // 30 days expiration

            // Store the token in the database
            $query = "UPDATE users SET login_token = $1 WHERE id = $2";
            pg_query_params($conn, $query, [$token, $user['id']]);

            echo "<script>
                if (confirm('Do you want to save this login for quick switching?')) {
                    let profiles = JSON.parse(localStorage.getItem('saved_profiles')) || [];
                    let exists = profiles.some(p => p.id === {$user['id']});
                    if (!exists) {
                        profiles.push({id: {$user['id']}, username: '{$user['username']}', profile_picture: '{$user['profile_picture']}'});
                        localStorage.setItem('saved_profiles', JSON.stringify(profiles));
                    }
                }
                window.location.href = 'index.php';
            </script>";
            exit();
        } else {
            echo "Invalid username or password.";
        }
    }
}

// Auto-login with cookie
if (isset($_COOKIE["login_token"])) {
    $token = $_COOKIE["login_token"];

    $query = "SELECT id, username, profile_picture FROM users WHERE login_token = $1";
    $result = pg_query_params($conn, $query, [$token]);

    if (pg_num_rows($result) > 0) {
        $user = pg_fetch_assoc($result);

        $_SESSION["user_id"] = $user['id'];
        $_SESSION["username"] = $user['username'];
        $_SESSION["profile_picture"] = $user['profile_picture'];

        header("Location: index.php");
        exit();
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Signup</title>
    <link rel="stylesheet" href="css/login/login.css">
</head>
<body>
    <div class="spline2">
        <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.68/build/spline-viewer.js"></script>
        <spline-viewer url="https://prod.spline.design/Ylco7b1CsvLH5v89/scene.splinecode"></spline-viewer>
    </div>

    <div class="wrapper">
        <input class="switch-btn" onclick="flipCard()" type="checkbox" name="checkbox" id="checkbox" />
        <label for="checkbox" class="label"></label>

        <div class="card-container">
            <div class="flip-card" id="flipCard">
                <div class="flip-card-inner">
                    <div class="flip-card-front">
                        <div class="title">Log in</div>
                        <form class="flip-card__form" action="login.php" method="POST">
                            <input class="flip-card__input" name="username" placeholder="Username" type="text">
                            <div class="password-container">
                                <div class="container flip-card__input" id="loginPassword">
                                    <img src="https://dl.dropbox.com/s/jxp9lsqokpw0k6p/lock-icon-01.svg?raw=1" id="lock">
                                    <input type="password" placeholder="Password" id="password">
                                    <div id="eye-wrapper" onclick="toggle()">
                                        <img src="https://dl.dropbox.com/s/g50832gcbpu0rh2/eye-open-01-01.svg?raw=1" id="open">
                                        <img src="https://dl.dropbox.com/s/r8obue865fk7e2z/eye-close-01-01.svg?raw=1" id="close">
                                    </div>
                                </div>
                            </div>
                            <label class="remember">
                                <input type="checkbox" name="remember" <?php echo isset($_COOKIE['remember_username']) ? 'checked' : ''; ?>> Remember Me
                                <br>
                                <button class="flip-card__btn" type="submit" name="action" value="login">Let’s go!</button>
                            </label>
                        </form>
                        <div class="forgot">
                            <button><a href="forgot.php">Forgot Username?</a></button>
                        </div>
                    </div>
                    <div class="flip-card-back">
                        <div class="title">Sign up</div>
                        <form class="flip-card__form" action="login.php" method="POST">
                            <input class="flip-card__input" name="email" placeholder="Email" type="email">
                            <input class="flip-card__input" id="signupPassword" name="password" placeholder="Password" type="password">
                            <button class="flip-card__btn" type="submit" name="action" value="register">Confirm!</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function flipCard() {
            document.getElementById("flipCard").classList.toggle("flipped");
        }

        function togglePassword(id) {
            var input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }

        var state = false;
        function toggle(){
            if(state){
                document.getElementById("password").setAttribute("type","password");
                document.getElementById("eye-wrapper").style.boxShadow = '0 0 0 0px white';
                document.getElementById("open").style.display= 'none';
                document.getElementById("close").style.display= 'block';
                document.getElementById("password").style.color = 'red'; // Reset color
                state = false;
            }
            else{
                document.getElementById("password").setAttribute("type","text");
                document.getElementById("eye-wrapper").style.boxShadow = '0 0 0 20px white';
                document.getElementById("password").style.color = 'white'; // Change text color to white
                document.getElementById("open").style.display= 'block';
                document.getElementById("close").style.display= 'none';
                state = true;
            }
        }
    </script>
</body>
</html>
