<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdmin($pdo)) { http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$ban = (int)($data['ban'] ?? 0);

if (!$id) { echo json_encode(['error' => 'Invalid user ID']); exit; }
if ($id == $_SESSION['user_id']) { echo json_encode(['error' => 'Cannot ban your own account']); exit; }

$stmt = $pdo->prepare("UPDATE users SET banned = ? WHERE id = ?");
$stmt->execute([$ban, $id]);
echo json_encode(['success' => true]);
?>