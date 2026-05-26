<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

$stmt = $pdo->prepare("
    SELECT p.id, p.title, p.image_path,
           (SELECT COALESCE(SUM(vote_value), 0) FROM votes WHERE post_id = p.id) as vote_score,
           u.username
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY vote_score DESC
    LIMIT 5
");
$stmt->execute();
echo json_encode($stmt->fetchAll());
?>