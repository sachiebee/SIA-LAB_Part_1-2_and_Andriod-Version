<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdmin($pdo)) { http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$nickname = trim($data['nickname'] ?? '');
$is_admin = (int)($data['is_admin'] ?? 0);

if (!$id || empty($username) || empty($email)) { echo json_encode(['error' => 'Missing required fields']); exit; }

$stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
$stmt->execute([$username, $email, $id]);
if ($stmt->fetch()) { echo json_encode(['error' => 'Username or email already taken']); exit; }

$stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, nickname = ?, is_admin = ? WHERE id = ?");
$stmt->execute([$username, $email, $nickname, $is_admin, $id]);
echo json_encode(['success' => true]);
?>