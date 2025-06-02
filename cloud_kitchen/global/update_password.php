<?php
session_start();
require_once __DIR__ .  '/../../DB_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$userId = $_SESSION['user_id'];
$oldPassword = $_POST['old-password'] ?? '';
$newPassword = $_POST['new-password'] ?? '';
$confirmPassword = $_POST['confirm-new-password'] ?? '';

if ($newPassword !== $confirmPassword) {
    die(json_encode(['success' => false, 'message' => 'Passwords do not match']));
}

if (strlen($newPassword) < 8) {
    die(json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']));
}

try {
    // Verify old password
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die(json_encode(['success' => false, 'message' => 'User not found']));
    }
    
    $user = $result->fetch_assoc();
    if (!password_verify($oldPassword, $user['password'])) {
        die(json_encode(['success' => false, 'message' => 'Incorrect current password']));
    }
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Password update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>