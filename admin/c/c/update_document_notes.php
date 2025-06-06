<?php
session_start();
require_once 'connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Admin not logged in']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['doc_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$doc_id = (int)$input['doc_id'];
$admin_notes = $input['admin_notes'] ?? null;
$admin_id = $_SESSION['admin_id'] ?? 1; // Default to admin user_id = 1

try {
    // Update document notes
    $query = "UPDATE kitchen_documents 
              SET admin_notes = ?, reviewed_by = ?, reviewed_at = NOW()
              WHERE doc_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $admin_notes, $admin_id, $doc_id);
    
    if ($stmt->execute()) {
        // Log admin action
        $action_type = !empty($admin_notes) ? 'Document Notes Added' : 'Document Notes Cleared';
        $action_target = "Document ID: $doc_id";
        
        $log_query = "INSERT INTO admin_actions (admin_id, action_type, action_target) VALUES (?, ?, ?)";
        $log_stmt = $conn->prepare($log_query);
        $log_stmt->bind_param("iss", $admin_id, $action_type, $action_target);
        $log_stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Document notes updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update document notes']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 