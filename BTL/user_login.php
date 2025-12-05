<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'inc/db.php';
require 'inc/functions.php';

$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

// (Xóa toàn bộ khối "Xử lý POST" ở đây vì đã chuyển sang login_action.php)

require 'inc/header.php';
?>
<h1>Đăng nhập</h1>
<p>Dùng tài khoản Học viên hoặc Admin để truy cập.</p>

<?php if ($error_message): ?>
    <div class="flash-message error"><?php echo h($error_message); ?></div>
<?php endif; ?>

<form action="login_action.php" method="POST" class="user-auth-form">
    <div class="form-group">
        <label for="login_credential"> Tên đăng nhập </label>
        <input type="text" id="login_credential" name="login_credential" required>
    </div>
    <div class="form-group">
        <label for="password">Mật khẩu</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Đăng nhập</button>
    </div>
    <p>Chưa có tài khoản? <a href="user_register.php">Đăng ký ngay</a></p>
</form>
<div style="height: 220px;">

</div>
<?php require 'inc/footer.php'; ?>