<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../inc/functions.php';

// Nếu đã đăng nhập, chuyển hướng tới dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$reason = $_GET['reason'] ?? '';
$error_message = '';

if ($reason === 'unauthorized') {
    $error_message = 'Bạn cần đăng nhập để truy cập.';
} elseif ($reason === 'timeout') {
    $error_message = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
} elseif ($reason === 'failed') {
    $error_message = 'Tên đăng nhập hoặc mật khẩu không đúng.';
} elseif ($reason === 'logout') {
    $error_message = 'Bạn đã đăng xuất thành công.';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Đăng nhập</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/admin.css">
    </head>
<body class="login-page">
    <div class="login-container">
        <h1>Đăng nhập Quản trị</h1>
        
        <?php if ($error_message): ?>
            <div class="flash-message <?php echo ($reason === 'logout' ? 'success' : 'error'); ?>">
                <?php echo h($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="login_action.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Đăng nhập</button>
            </div>
        </form>
        <a href="../index.php" class="back-link">&laquo; Quay lại trang chủ</a>
    </div>
</body>
</html>