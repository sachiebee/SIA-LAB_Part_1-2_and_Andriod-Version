<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdmin($pdo)) { http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');
$slug = trim($data['slug'] ?? '');

if (empty($name) || empty($slug)) { echo json_encode(['error' => 'Name and slug are required']); exit; }

$stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
$stmt->execute([$slug]);
if ($stmt->fetch()) { echo json_encode(['error' => 'Slug already exists']); exit; }

$stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
$stmt->execute([$name, $slug]);
echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
?>