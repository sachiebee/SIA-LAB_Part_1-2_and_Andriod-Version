<?php http_response_code(403); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied | MemeVerse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #fef9e8; font-family: 'Poppins', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .denied-card { background: white; border-radius: 32px; padding: 2rem; max-width: 500px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .denied-code { font-size: 5rem; font-weight: 800; background: linear-gradient(135deg, #f59e0b, #ff8e8e); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .btn-primary-custom { background: linear-gradient(135deg, #f59e0b, #ff8e8e); color: white; padding: 0.75rem 1.75rem; border-radius: 60px; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <div class="denied-card">
        <div class="denied-code">403</div>
        <h1>🚫 Access Denied</h1>
        <p>You don't have permission to access this page.<br>Please navigate through the main website.</p>
        <a href="index.php" class="btn-primary-custom"><i class="bi bi-house-door-fill"></i> Back to Home</a>
    </div>
</body>
</html>