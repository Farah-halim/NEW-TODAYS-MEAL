<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

try {
    $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $tag = isset($_GET['tag']) ? (int)$_GET['tag'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;

    $query = "
        SELECT c.*, u.u_name, u.phone, u.mail, cat.c_name as category_name,
               GROUP_CONCAT(dt.tag_name) as tags
        FROM cloud_kitchen_owner c
        JOIN users u ON c.user_id = u.user_id
        JOIN category cat ON c.category_id = cat.cat_id
        LEFT JOIN caterer_tags ct ON c.user_id = ct.user_id
        LEFT JOIN dietary_tags dt ON ct.tag_id = dt.tag_id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($category) {
        $query .= " AND c.category_id = ?";
        $params[] = $category;
    }
    
    if ($tag) {
        $query .= " AND EXISTS (SELECT 1 FROM caterer_tags WHERE user_id = c.user_id AND tag_id = ?)";
        $params[] = $tag;
    }
    
    if ($status) {
        $query .= " AND c.status = ?";
        $params[] = $status;
    }
    
    $query .= " GROUP BY c.user_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $kitchens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($kitchens);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
