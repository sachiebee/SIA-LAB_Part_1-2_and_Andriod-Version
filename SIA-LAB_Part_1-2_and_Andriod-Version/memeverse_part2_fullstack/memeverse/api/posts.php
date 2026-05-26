<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

$limit = 10;
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$sql = "
    SELECT p.*, u.username, u.avatar, u.nickname, u.id as user_id,
           c.name as category_name, c.slug as category_slug,
           (SELECT COALESCE(SUM(vote_value), 0) FROM votes WHERE post_id = p.id) as vote_score,
           (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.id
    JOIN categories c ON p.category_id = c.id
";

$params = [];

if ($category_id > 0) {
    $sql .= " WHERE p.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

if ($last_id > 0) {
    $sql .= (strpos($sql, 'WHERE') !== false ? " AND p.id < :last_id" : " WHERE p.id < :last_id");
    $params[':last_id'] = $last_id;
}

$sql .= " ORDER BY p.created_at DESC, p.id DESC LIMIT :limit";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();

$posts = $stmt->fetchAll();

foreach ($posts as &$post) {
    $post['user_vote'] = getUserVote($pdo, $post['id'], $_SESSION['user_id'] ?? 0);
}

echo json_encode($posts);
?>