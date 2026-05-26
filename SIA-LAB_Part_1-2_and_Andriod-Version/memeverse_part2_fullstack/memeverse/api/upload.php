<?php
define('API_ACCESS', true);
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Enable error logging for debugging
error_log("Upload API called");

// Check if user is logged in
if (!isLoggedIn()) {
    error_log("Upload API: User not logged in");
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit;
}

error_log("Upload API: User ID: " . $_SESSION['user_id']);

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error_msg = isset($_FILES['image']) ? "Upload error code: " . $_FILES['image']['error'] : "No file uploaded";
    error_log("Upload API: " . $error_msg);
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['image'];
error_log("Upload API: File received: " . $file['name'] . ", Size: " . $file['size']);

// Get form data
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 0);

error_log("Upload API: Title: $title, Category: $category_id");

if ($category_id <= 0) {
    echo json_encode(['error' => 'Please select a category']);
    exit;
}

// Validate image using finfo (more secure than $_FILES['type'])
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mime, $allowed)) {
    error_log("Upload API: Invalid MIME type: " . $mime);
    echo json_encode(['error' => 'Only JPG, PNG, GIF, WEBP images are allowed']);
    exit;
}

// Validate file size
if ($file['size'] > MAX_FILE_SIZE) {
    error_log("Upload API: File too large: " . $file['size'] . " bytes");
    echo json_encode(['error' => 'File too large (max 5MB)']);
    exit;
}

// Create uploads directory if it doesn't exist
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
    error_log("Upload API: Created uploads directory");
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $ext;
$destination = UPLOAD_DIR . $filename;

error_log("Upload API: Destination: " . $destination);

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $destination)) {
    error_log("Upload API: File moved successfully");
    $image_path = 'uploads/' . $filename;
    
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, category_id, title, description, image_path) VALUES (?, ?, ?, ?, ?)");
    $result = $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $image_path]);
    
    if ($result) {
        $post_id = $pdo->lastInsertId();
        error_log("Upload API: Post created with ID: " . $post_id);
        echo json_encode(['success' => true, 'post_id' => $post_id]);
    } else {
        error_log("Upload API: Database insert failed");
        echo json_encode(['error' => 'Failed to save to database']);
    }
} else {
    error_log("Upload API: move_uploaded_file failed");
    echo json_encode(['error' => 'Failed to save file']);
}
?>