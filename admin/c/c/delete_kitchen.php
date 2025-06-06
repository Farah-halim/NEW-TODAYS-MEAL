<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

require 'db_connect.php';

header('Content-Type: application/json');

try {
    $kitchenId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$kitchenId) throw new Exception("Invalid kitchen ID");

    $pdo->beginTransaction();

    // 1. Delete from junction tables first
    $pdo->exec("DELETE FROM cloud_kitchen_specialist_category WHERE cloud_kitchen_id = $kitchenId");
    $pdo->exec("DELETE FROM caterer_tags WHERE user_id = $kitchenId");
    
    // 2. Delete meals and related data
    $mealIds = $pdo->query("SELECT meal_id FROM meals WHERE cloud_kitchen_id = $kitchenId")->fetchAll(PDO::FETCH_COLUMN);
    if ($mealIds) {
        $mealIdsStr = implode(',', $mealIds);
        $pdo->exec("DELETE FROM meal_category WHERE meal_id IN ($mealIdsStr)");
        $pdo->exec("DELETE FROM meal_tags WHERE meal_id IN ($mealIdsStr)");
        $pdo->exec("DELETE FROM meals WHERE cloud_kitchen_id = $kitchenId");
    }

    // 3. Delete from cloud_kitchen_owner
    //$pdo->exec("DELETE FROM cloud_kitchen_owner WHERE user_id = $kitchenId");
    
    // 4. Finally delete from users table
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$kitchenId]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Kitchen not found or already deleted");
    }

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Kitchen and all related data deleted successfully'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}