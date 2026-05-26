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
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

if (!$post_id) {
    echo json_encode(['error' => 'Invalid post ID']);
    exit;
}

if ($category_id <= 0) {
    echo json_encode(['error' => 'Please select a category']);
    exit;
}

$stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post || $post['user_id'] != $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

$stmt = $pdo->prepare("UPDATE posts SET title = ?, description = ?, category_id = ? WHERE id = ?");
$stmt->execute([$title, $description, $category_id, $post_id]);

echo json_encode(['success' => true]);
?>