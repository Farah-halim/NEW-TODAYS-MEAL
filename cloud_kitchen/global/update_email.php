<?php
session_start();
require_once __DIR__ .  '/../../DB_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$userId = $_SESSION['user_id'];
$newEmail = $_POST['new-email'] ?? '';
$confirmEmail = $_POST['confirm-new-email'] ?? '';

if ($newEmail !== $confirmEmail) {
    die(json_encode(['success' => false, 'message' => 'Emails do not match']));
}

if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['success' => false, 'message' => 'Invalid email format']));
}

try {
    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE mail = ? AND user_id != ?");
    $stmt->bind_param("si", $newEmail, $userId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        die(json_encode(['success' => false, 'message' => 'Email already in use']));
    }
    
    // Update email
    $stmt = $conn->prepare("UPDATE users SET mail = ? WHERE user_id = ?");
    $stmt->bind_param("si", $newEmail, $userId);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Email update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>