
<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

try {
    $tagId = isset($_GET['tag_id']) ? (int)$_GET['tag_id'] : 0;
    
    $stmt = $pdo->prepare("
        SELECT cko.*, c.c_name as category_name 
        FROM cloud_kitchen_owner cko
        JOIN caterer_tags ct ON cko.user_id = ct.user_id
        JOIN cloud_kitchen_specialist_category cksc ON cko.user_id = cksc.cloud_kitchen_id
        JOIN category c ON cksc.cat_id = c.cat_id
        WHERE ct.tag_id = ?
        LIMIT 5
    ");
    
    $stmt->execute([$tagId]);
    $kitchens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($kitchens);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
