<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header("Location: index.php?reason=failed");
    exit;
}

try {
    // Tìm admin theo username
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    // === THAY ĐỔI QUAN TRỌNG NẰM Ở ĐÂY ===
    // Thay vì dùng password_verify, chúng ta so sánh mật khẩu dạng TEXT thường
    // Cảnh báo: CỰC KỲ KHÔNG AN TOÀN!
    if ($admin && $password === $admin['password_hash']) {
        // Đăng nhập thành công
        session_regenerate_id(true); // Chống tấn công Session Fixation
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['last_activity'] = time();
        
        header("Location: dashboard.php");
        exit;
    } else {
        // Sai thông tin
        header("Location: index.php?reason=failed");
        exit;
    }

} catch (PDOException $e) {
    // Lỗi CSDL
    die("Lỗi CSDL: " . $e->getMessage());
}
?>