<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
echo json_encode(['count' => (int)$stmt->fetchColumn()]);
?>