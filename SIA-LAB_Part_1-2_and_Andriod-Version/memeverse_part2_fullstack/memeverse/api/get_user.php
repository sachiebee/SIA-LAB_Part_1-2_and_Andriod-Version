<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    echo json_encode(['error' => 'Missing user ID', 'success' => false]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, nickname, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['error' => 'User not found', 'success' => false]);
    exit;
}

$user['avatar_url'] = getUserAvatar($user, 48);
echo json_encode(['success' => true, 'user' => $user]);
?>