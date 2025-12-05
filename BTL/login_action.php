<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'inc/db.php';
require 'inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: user_login.php");
    exit;
}

$login_credential = trim($_POST['login_credential'] ?? '');
$password = $_POST['password'] ?? ''; 

if (empty($login_credential) || empty($password)) {
    $_SESSION['error_message'] = "Vui lòng nhập đầy đủ thông tin.";
    header("Location: user_login.php");
    exit;
}

try {
    // --- 1. KIỂM TRA ADMIN TRƯỚC ---
    $stmt_admin = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt_admin->execute([$login_credential]);
    $admin = $stmt_admin->fetch();

    if ($admin && $password === $admin['password_hash']) {
        // --- ĐÂY LÀ ADMIN ---
        session_regenerate_id(true);
        
        // (QUAN TRỌNG) KHÔNG XÓA SESSION USER NỮA
        // Để Admin và User có thể cùng tồn tại song song
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['last_activity'] = time();
        
        header("Location: admin/dashboard.php");
        exit;
    }

    // --- 2. KIỂM TRA USER ---
    $stmt_user = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt_user->execute([$login_credential]);
    $user = $stmt_user->fetch();

    if ($user && $password === $user['password_hash']) {
        if ($user['status'] === 'locked') {
            $_SESSION['error_message'] = "Tài khoản của bạn đã bị khóa.";
            header("Location: user_login.php");
            exit;
        }

        // --- ĐÂY LÀ USER ---
        session_regenerate_id(true);
        
        // (QUAN TRỌNG) KHÔNG XÓA SESSION ADMIN NỮA
        // Để Admin vẫn sống ở tab bên kia
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        
        add_flash_message("Đăng nhập thành công!", 'success');
        header("Location: index.php");
        exit;
    }

    $_SESSION['error_message'] = "Tên đăng nhập, email hoặc mật khẩu không đúng.";
    header("Location: user_login.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Lỗi CSDL: " . $e->getMessage();
    header("Location: user_login.php");
    exit;
}
?>