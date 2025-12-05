<?php
// inc/auth.php
// Kiểm tra xác thực admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Thiết lập thời gian timeout cho session (ví dụ: 30 phút)
$timeout_duration = 1800; // 30 * 60 giây

if (isset($_SESSION['admin_id'])) {
    // Kiểm tra thời gian đăng nhập cuối cùng
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        // Hết hạn session
        session_unset();
        session_destroy();
        header("Location: index.php?reason=timeout");
        exit;
    }
    // Cập nhật thời gian hoạt động
    $_SESSION['last_activity'] = time();
} else {
    // Chưa đăng nhập
    header("Location: index.php?reason=unauthorized");
    exit;
}
?>