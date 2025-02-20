<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "You must be logged in."]));
}

$user_id = $_SESSION["user_id"];

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == "add") {
        $name = trim($_POST["name"]);
        $phone = trim($_POST["phone"]);
        $group = trim($_POST["group"]);

        if ($name === "" || $phone === "") {
            exit(json_encode(["error" => "Name and phone are required."]));
        }

        $stmt = $conn->prepare("INSERT INTO contacts (name, phone, group_name, user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $phone, $group, $user_id);
        if ($stmt->execute()) {
            exit(json_encode(["success" => true]));
        } else {
            exit(json_encode(["error" => "Failed to add contact."]));
        }
    } elseif ($action == "delete") {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        exit(json_encode(["success" => true]));
    } elseif ($action == "rename") {
        $id = $_POST['id'];
        $new_name = trim($_POST['new_name']);
        if ($new_name === "") {
            exit(json_encode(["error" => "New name cannot be empty."]));
        }
        $stmt = $conn->prepare("UPDATE contacts SET name = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $new_name, $id, $user_id);
        $stmt->execute();
        exit(json_encode(["success" => true]));
    } elseif ($action == "update_group") {
        $id = $_POST['id'];
        $new_group = trim($_POST['new_group']);
        $stmt = $conn->prepare("UPDATE contacts SET group_name = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $new_group, $id, $user_id);
        $stmt->execute();
        exit(json_encode(["success" => true]));
    }
}

$stmt = $conn->prepare("SELECT * FROM contacts WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$contacts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

echo json_encode($contacts);
exit();
