<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($login) || empty($password)) {
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
$stmt->execute([$login, $login]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Check if banned
    $stmt = $pdo->prepare("SELECT banned FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $banned = $stmt->fetchColumn();
    
    if ($banned) {
        echo json_encode(['error' => 'Your account has been banned. Contact support.']);
        exit;
    }
    
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Invalid credentials']);
}
?>