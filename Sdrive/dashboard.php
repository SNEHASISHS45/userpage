<?php
session_start();
require 'config.php'; // Include database connection
require 'vendor/autoload.php'; // Ensure Cloudinary SDK is loaded

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;

// Initialize Cloudinary
$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dzn369qpk',
        'api_key'    => '274266766631951',
        'api_secret' => 'ThwRkNdXKQ2LKnQAAukKgmo510g',
    ],
    'url' => [
        'secure' => true // Ensure HTTPS URLs
    ]
]);

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_data = null;

// Fetch user data
if ($user_id) {
    $sql = "SELECT * FROM users WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = password_hash(mysqli_real_escape_string($conn, $_POST['password']), PASSWORD_DEFAULT);

    // Upload to Cloudinary if a new profile picture is provided
    $profile_pic = $user_data['profile_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        try {
            $uploadedFile = $cloudinary->uploadApi()->upload($_FILES['profile_pic']['tmp_name'], [
                'folder' => 'sdrive_backup/profile_pics'
            ]);
            $profile_pic = $uploadedFile['secure_url'];
        } catch (Exception $e) {
            die("Error uploading image: " . $e->getMessage());
        }
    }

    // Update user data using prepared statements
    $update_sql = "UPDATE users SET username=?, email=?, phone=?, password=?, profile_pic=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "sssssi", $username, $email, $phone, $password, $profile_pic, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>sendNotification('Profile Updated', 'Your profile details have been successfully updated.');</script>";
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>SDrive - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard/dashboard.css">
</head>

<body>
    <div class="card">
        <div class="dashboard-header">
            <h1 class="dashboard-title">SDrive Dashboard</h1>
            <div class="profile-section">
                <img src="<?php echo !empty($user_data['profile_pic']) ? $user_data['profile_pic'] : 'default-avatar.png'; ?>" alt="Profile Picture" class="profile-pic" id="profile-pic">

                <div class="profile-info">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input class="input" type="text" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" placeholder="Username" required>
                        <input class="input" type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" placeholder="Email" required>
                        <input class="input" type="text" name="phone" value="<?php echo htmlspecialchars($user_data['phone']); ?>" placeholder="Phone" required>
                        <input class="input" type="password" name="password" placeholder="New Password">
                        <input class="fileup" type="file" name="profile_pic" accept="image/*">
                        <button type="submit">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="dashboard-sections">
            <div class="section">
                <h2>Photo Gallery</h2>
                <p>View and manage your photo gallery.</p>
                <a href="photos.php" class="button">Go to Gallery</a>
            </div>
            <div class="section">
                <h2>Contacts Backup</h2>
                <p>Backup your contacts to our secure storage.</p>
                <a href="contacts.php" class="button">Backup Contacts</a>
            </div>
            <div class="section">
                <h2>Documents Backup</h2>
                <p>Backup your documents securely.</p>
                <a href="documents.php" class="button">Backup Documents</a>
            </div>
            <div class="section">
                <h2>Vault</h2>
                <p>Store your sensitive files securely.</p>
                <a href="vaults.php" class="button">Access Vault</a>
            </div>
        </div>
    </div>

    <script>
        function sendNotification(title, message) {
            if (Notification.permission === "granted") {
                new Notification(title, {
                    body: message,
                    icon: "default-avatar.png"
                });
            }
        }

        if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    sendNotification("Welcome to SDrive!", "You have new notifications!");
                }
            });
        }
    </script>
</body>

</html>
