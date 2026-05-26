<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$receiver_id = (int)($data['receiver_id'] ?? 0);
$message = trim($data['message'] ?? '');

if (!$receiver_id || empty($message)) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $receiver_id, $message]);
    $msg_id = $pdo->lastInsertId();
    
    $notify = $pdo->prepare("INSERT INTO notifications (user_id, type, source_id, actor_id) VALUES (?, 'message', ?, ?)");
    $notify->execute([$receiver_id, $msg_id, $_SESSION['user_id']]);
    
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Send message error: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to send message']);
}
?>