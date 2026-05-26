<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/email_helper.php';

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');

if (empty($email)) {
    echo json_encode(['error' => 'Email is required']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => true]);
    exit;
}

$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

$stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$user['id'], $token, $expires]);

$resetLink = SITE_URL . "reset_password.php?token=$token";
$subject = "Reset your MemeVerse password";
$htmlBody = "<h2>Reset Your Password</h2><p>Click <a href='$resetLink'>here</a> to reset your password. Link expires in 1 hour.</p>";

mail($email, $subject, $htmlBody);
echo json_encode(['success' => true]);
?>