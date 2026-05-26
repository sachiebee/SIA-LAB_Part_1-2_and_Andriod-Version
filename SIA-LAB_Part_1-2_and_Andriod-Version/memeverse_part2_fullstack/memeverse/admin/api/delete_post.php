<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit;
}

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

if (!$post_id) {
    echo json_encode(['error' => 'Invalid post ID']);
    exit;
}

$stmt = $pdo->prepare("SELECT user_id, image_path FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post || $post['user_id'] != $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

$image_path = __DIR__ . '/../' . $post['image_path'];
if (file_exists($image_path)) {
    unlink($image_path);
}

$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
$stmt->execute([$post_id]);

echo json_encode(['success' => true]);
?>