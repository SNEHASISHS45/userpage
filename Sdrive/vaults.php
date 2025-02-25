<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "config.php";

if (!isset($_SESSION["user_id"])) {
    die("Access denied. Please log in.");
}

$user_id = $_SESSION["user_id"];

$user_query = $conn->query("SELECT username, pin_hash FROM users WHERE id = '$user_id'");
$user = $user_query->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["set_pin"])) {
        $pin = $_POST["pin"];
        $pin_hash = password_hash($pin, PASSWORD_BCRYPT);
        $conn->query("UPDATE users SET pin_hash = '$pin_hash' WHERE id = '$user_id'");
        echo "<script>showNotification('PIN set successfully!');</script>";
    }

    if (isset($_POST["verify_pin"])) {
        $pin = $_POST["pin"];
        if (password_verify($pin, $user["pin_hash"])) {
            $_SESSION["vault_access"] = true;
        } else {
            echo "<script>showNotification('Invalid PIN!');</script>";
        }
    }

    if (isset($_POST["delete_selected"])) {
        $file_ids = $_POST["file_ids"] ?? [];
        if (!empty($file_ids)) {
            $file_ids_str = implode(",", array_map('intval', $file_ids));
            $conn->query("DELETE FROM vault_items WHERE user_id = '$user_id' AND id IN ($file_ids_str)");
            echo "<script>showNotification('Selected files deleted!');</script>";
        }
    }

    if (isset($_FILES["files"])) {
        foreach ($_FILES["files"]["tmp_name"] as $key => $tmp_name) {
            $file_name = basename($_FILES["files"]["name"][$key]);
            $file_path = "uploads/" . $user_id . "_" . $file_name;

            $check_duplicate = $conn->query("SELECT id FROM vault_items WHERE user_id = '$user_id' AND file_name = '$file_name'");
            if ($check_duplicate->num_rows == 0) {
                if (move_uploaded_file($tmp_name, $file_path)) {
                    $conn->query("INSERT INTO vault_items (user_id, file_name, file_path) VALUES ('$user_id', '$file_name', '$file_path')");
                }
            }
        }
        echo "<script>showNotification('Files uploaded successfully!');</script>";
    }
}

$vault_items = $conn->query("SELECT * FROM vault_items WHERE user_id = '$user_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Vault</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <link rel="stylesheet" href="css/vaults/vaults.css">
</head>
<body>
<div class="container">
    <div id="notification" class="notification"></div>
    <h2>🔒 Welcome to Your Vault, <?php echo htmlspecialchars($user['username']); ?>!</h2>

    <?php if (!$user["pin_hash"]): ?>
        <h3>Set Your Vault PIN</h3>
        <form method="POST">
            <label>Enter PIN:</label>
            <input type="password" name="pin" id="pin" required>
            <button type="button" onclick="togglePinVisibility()">👁️</button>
            <button type="submit" name="set_pin">Set PIN</button>
        </form>
    <?php elseif (!isset($_SESSION["vault_access"])): ?>
        <h3>Enter Your Vault PIN</h3>
        <form method="POST">
            <label>PIN:</label>
            <input type="password" name="pin" id="pin" required>
            <button type="button" onclick="togglePinVisibility()">👁️</button>
            <button type="submit" name="verify_pin">Access Vault</button>
        </form>
    <?php else: ?>
        <h3>Upload Your Files</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="files[]" multiple required>
            <button type="submit">Upload Files</button>
        </form>

        <h3>Your Vault Items:</h3>
        <form method="POST">
            <button type="submit" name="delete_selected">Delete Selected</button>
            <button type="button" id="select_all">Select All</button>
            <div class="vault-grid">
                <?php while ($item = $vault_items->fetch_assoc()): ?>
                    <div class="file-item">
                        <input type="checkbox" name="file_ids[]" value="<?php echo $item['id']; ?>">
                        <a href="<?php echo htmlspecialchars($item['file_path']); ?>" target="_blank">
                            📄 <?php echo htmlspecialchars($item['file_name']); ?>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    function togglePinVisibility() {
        const pinInput = document.getElementById("pin");
        pinInput.type = pinInput.type === "password" ? "text" : "password";
    }

    function showNotification(message) {
        const notification = document.getElementById("notification");
        notification.textContent = message;
        notification.style.display = "block";
        gsap.fromTo(notification, {opacity: 0, y: -20}, {opacity: 1, y: 0, duration: 0.5});
        setTimeout(() => {
            gsap.to(notification, {opacity: 0, y: -20, duration: 0.5, onComplete: () => {
                notification.style.display = "none";
            }});
        }, 3000);
    }

    $(document).ready(function () {
        $("#select_all").click(function () {
            $("input[type='checkbox']").prop("checked", true);
        });

        gsap.from(".container", {opacity: 0, y: -50, duration: 1});
        gsap.to(".file-item", {opacity: 1, stagger: 0.1});
    });
</script>
</body>
</html>