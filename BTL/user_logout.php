<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'inc/functions.php';

// 1. Xóa thông tin User
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);

// 2. (QUAN TRỌNG) Xóa luôn giỏ hàng khi đăng xuất
unset($_SESSION['cart']);

// 3. Xóa thông báo lỗi/flash message cũ (nếu có) để sạch sẽ
unset($_SESSION['errors']);
unset($_SESSION['old_input']);
unset($_SESSION['flash_messages']);

add_flash_message("Bạn đã đăng xuất thành công.", 'success');
header("Location: index.php");
exit;
?>