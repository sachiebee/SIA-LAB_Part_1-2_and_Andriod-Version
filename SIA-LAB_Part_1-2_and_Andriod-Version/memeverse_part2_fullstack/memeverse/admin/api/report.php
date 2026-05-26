<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Please login']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$target_id = (int)($data['id'] ?? 0);
$reason = trim($data['reason'] ?? '');

if (!$type || !$target_id || !$reason) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

if ($type === 'user') {
    $stmt = $pdo->prepare("INSERT INTO reports (user_id, reported_user_id, reason) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $target_id, $reason]);
} elseif ($type === 'post') {
    $stmt = $pdo->prepare("INSERT INTO reports (user_id, reported_post_id, reason) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $target_id, $reason]);
} else {
    echo json_encode(['error' => 'Invalid type']);
    exit;
}

echo json_encode(['success' => true]);
?>