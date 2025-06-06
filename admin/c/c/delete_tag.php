
<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tag_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM dietary_tags WHERE tag_id = ?");
        $stmt->execute([$_POST['tag_id']]);
        $_SESSION['success'] = 'Tag deleted successfully';
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Could not delete tag: ' . $e->getMessage();
    }
}

header('Location: manage_categories.php');
exit;
