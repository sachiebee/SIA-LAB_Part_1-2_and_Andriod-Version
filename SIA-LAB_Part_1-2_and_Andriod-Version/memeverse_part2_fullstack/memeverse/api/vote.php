<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Log the request for debugging
error_log("=== VOTE API CALLED ===");
error_log("POST data: " . print_r($_POST, true));

// Check if user is logged in
if (!isset($_SESSION) || !isLoggedIn()) {
    error_log("Vote API: User not logged in");
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit;
}

// Get POST data
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$vote = isset($_POST['vote']) ? $_POST['vote'] : '';

error_log("Vote API: post_id=$post_id, vote=$vote, user_id=" . $_SESSION['user_id']);

// Validate inputs
if (!$post_id) {
    error_log("Vote API: Invalid post ID");
    echo json_encode(['error' => 'Invalid post ID']);
    exit;
}

if (!in_array($vote, ['up', 'down'])) {
    error_log("Vote API: Invalid vote type: $vote");
    echo json_encode(['error' => 'Invalid vote type']);
    exit;
}

$vote_value = ($vote === 'up') ? 1 : -1;
$user_id = $_SESSION['user_id'];

// Get post owner
try {
    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    
    if (!$post) {
        error_log("Vote API: Post not found: $post_id");
        echo json_encode(['error' => 'Post not found']);
        exit;
    }
    
    error_log("Vote API: Post owner: " . $post['user_id']);
} catch (PDOException $e) {
    error_log("Vote API DB error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
    exit;
}

// Check if user already voted
try {
    $stmt = $pdo->prepare("SELECT id, vote_value FROM votes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $existing = $stmt->fetch();
    
    error_log("Vote API: Existing vote: " . print_r($existing, true));
    
    if ($existing) {
        if ($existing['vote_value'] == $vote_value) {
            // Same vote - remove it
            $stmt = $pdo->prepare("DELETE FROM votes WHERE id = ?");
            $stmt->execute([$existing['id']]);
            error_log("Vote API: Removed existing vote");
        } else {
            // Different vote - update
            $stmt = $pdo->prepare("UPDATE votes SET vote_value = ? WHERE id = ?");
            $stmt->execute([$vote_value, $existing['id']]);
            error_log("Vote API: Updated vote from {$existing['vote_value']} to $vote_value");
        }
    } else {
        // New vote
        $stmt = $pdo->prepare("INSERT INTO votes (post_id, user_id, vote_value) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $user_id, $vote_value]);
        error_log("Vote API: Inserted new vote");
    }
    
    // Get updated vote score
    $new_score = getVoteScore($pdo, $post_id);
    error_log("Vote API: New score: $new_score");
    
    echo json_encode(['success' => true, 'new_score' => $new_score]);
    
} catch (PDOException $e) {
    error_log("Vote API Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>