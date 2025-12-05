<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CHỈ XÓA thông tin của Admin
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
// KHÔNG DÙNG session_destroy(); vì nó sẽ xóa luôn cả tài khoản User (nếu đang đăng nhập)

// Chuyển hướng về trang đăng nhập Admin
header("Location: index.php?reason=logout");
exit;
?>