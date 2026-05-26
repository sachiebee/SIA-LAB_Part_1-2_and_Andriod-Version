<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdmin($pdo)) { http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);

if (!$id) { echo json_encode(['error' => 'Invalid category ID']); exit; }

$stmt = $pdo->prepare("UPDATE posts SET category_id = NULL WHERE category_id = ?");
$stmt->execute([$id]);
$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$stmt->execute([$id]);
echo json_encode(['success' => true]);
?>