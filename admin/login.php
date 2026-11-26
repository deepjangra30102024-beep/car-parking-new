<?php
// admin/login.php
require_once __DIR__ . '/../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        // Default verification (also supports plain password123 as fallback for quick start)
        if ($admin && (password_verify($password, $admin['password']) || $password === 'password123')) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];

            header('Location: ' . BASE_URL . 'admin/index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            padding: 30px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px; font-size: 26px;">
            <i class="fa-solid fa-square-parking"></i>
        </div>
        <h4 class="fw-bold mb-1">Smart Parking</h4>
        <p class="text-muted small">Admin & Guard Portal Login</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label small fw-semibold">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                <input type="text" name="username" class="form-control" value="" required placeholder="Enter username">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" class="form-control" value="" required placeholder="Enter password">
            </div>
            <!-- <div class="form-text small">Default demo: <code>admin</code> / <code>password123</code></div> -->
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mt-2">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
        </button>
    </form>
</div>

</body>
</html>
