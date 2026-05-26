<?php
http_response_code($error_code ?? 500);
$error_title = $error_title ?? 'Oops! Something went wrong';
$error_message = $error_message ?? 'We encountered an unexpected issue. Please try again later.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error | MemeVerse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fef9e8; font-family: 'Poppins', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { background: white; border-radius: 32px; padding: 2rem; max-width: 500px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .btn-primary-custom { background: linear-gradient(135deg, #ff6b6b, #ff8e8e); color: white; padding: 0.75rem 1.75rem; border-radius: 60px; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <div class="error-card">
        <div style="font-size: 4rem;">😵</div>
        <h1><?= htmlspecialchars($error_title) ?></h1>
        <p><?= htmlspecialchars($error_message) ?></p>
        <a href="index.php" class="btn-primary-custom"><i class="bi bi-house-door-fill"></i> Back to Home</a>
    </div>
</body>
</html>