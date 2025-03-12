<?php
// Turn off error display for production
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set header to return JSON early
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sdrive";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Handle GET requests (fetch contacts)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $userId = $_SESSION['user_id'];
        
        // Check if contacts table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'contacts'");
        if ($tableCheck->num_rows == 0) {
            // Create contacts table if it doesn't exist
            $createTable = "CREATE TABLE contacts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                email VARCHAR(100),
                group_name VARCHAR(50),
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_id)
            )";
            $conn->query($createTable);
        }
        
        $sql = "SELECT * FROM contacts WHERE user_id = ? ORDER BY name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $contacts = [];
        while ($row = $result->fetch_assoc()) {
            $contacts[] = $row;
        }
        
        echo json_encode($contacts);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching contacts: ' . $e->getMessage()]);
    }
    exit();
}

// Handle POST requests (add, update, delete, import)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get JSON data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            exit();
        }
        
        $userId = $_SESSION['user_id'];
        $action = $data['action'] ?? '';
        
        // Add contact
        if ($action === 'add' && isset($data['contact'])) {
            $contact = $data['contact'];
            
            // Validate required fields
            if (empty($contact['name']) || empty($contact['phone'])) {
                echo json_encode(['success' => false, 'message' => 'Name and phone are required']);
                exit();
            }
            
            // Make sure email is not null if empty
            $email = !empty($contact['email']) ? $contact['email'] : '';
            $group = !empty($contact['group_name']) ? $contact['group_name'] : '';
            $notes = !empty($contact['notes']) ? $contact['notes'] : '';
            
            $sql = "INSERT INTO contacts (user_id, name, phone, email, group_name, notes) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssss", 
                $userId, 
                $contact['name'], 
                $contact['phone'], 
                $email, 
                $group, 
                $notes
            );
            
            if ($stmt->execute()) {
                $contact['id'] = $conn->insert_id;
                echo json_encode(['success' => true, 'contact' => $contact]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
            }
            exit();
        }
        
        // Rest of your code for update, delete, import...
        
        // If we get here, the action was not recognized
        echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit();
}

// If we get here, the request method is not supported
echo json_encode(['success' => false, 'message' => 'Method not allowed: ' . $_SERVER['REQUEST_METHOD']]);
$conn->close();
?>