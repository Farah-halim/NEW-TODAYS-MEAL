
<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

try {
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
    
    $stmt = $pdo->prepare("
        SELECT cko.*, u.u_name, u.phone, u.mail
        FROM cloud_kitchen_owner cko
        JOIN users u ON cko.user_id = u.user_id
        JOIN cloud_kitchen_specialist_category cksc ON cko.user_id = cksc.cloud_kitchen_id
        WHERE cksc.cat_id = ?
        LIMIT 5
    ");
    
    $stmt->execute([$categoryId]);
    $kitchens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($kitchens);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
