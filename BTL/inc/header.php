<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'inc/functions.php';

// Kiểm tra trang
$current_page = basename($_SERVER['SCRIPT_NAME']);
$is_profile_page = ($current_page === 'profile.php');
$is_invoice_page = ($current_page === 'invoice.php');

// Kiểm tra trạng thái
$is_admin_logged_in = isset($_SESSION['admin_id']);
$is_user_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký khóa học Online</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/public.css">
    
    <?php if ($is_profile_page): ?>
    <link rel="stylesheet" href="css/profile.css">
    <?php endif; ?>
    
    <?php if ($is_invoice_page): ?>
    <link rel="stylesheet" href="css/invoice.css">
    <?php endif; ?>

    <style>
        .admin-badge-link {
            background-color: var(--dark-color); color: #ffc107 !important;
            padding: 5px 12px; border-radius: 4px; font-size: 0.85rem;
            font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;
            border: 1px solid #ffc107;
        }
        .admin-badge-link:hover { background-color: #ffc107; color: #000 !important; }
    </style>
</head>
<body class="<?php echo $is_profile_page ? 'profile-page-wrapper' : ($is_invoice_page ? 'invoice-page-wrapper' : ''); ?>">
    <div class="page-wrapper">
        <header class="main-header">
            <div class="container">
                <a href="index.php" class="logo">Khóa Học Online</a>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php">Trang chủ</a></li>
                        
                        <?php if ($is_user_logged_in): ?>
                            <li>
                                <a href="cart.php" id="cart-icon-container" style="display: flex; align-items: center; gap: 5px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
                                    Giỏ hàng 
                                    <span id="cart-count" class="cart-badge">
                                        <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                                    </span>
                                </a>
                            </li>
                            <li><a href="profile.php">Trang cá nhân</a></li>
                            <li><span class="welcome-user">Chào, <?php echo h($_SESSION['user_name']); ?></span></li>
                            <li><a href="user_logout.php">Đăng xuất</a></li>

                        <?php elseif ($is_admin_logged_in): ?>
                            <li>
                                <a href="admin/dashboard.php" class="admin-badge-link">
                                    Panel Quản trị
                                </a>
                            </li>
                            <li><a href="user_login.php">Đăng nhập User</a></li>

                        <?php else: ?>
                            <li><a href="user_login.php">Đăng nhập</a></li>
                            <li><a href="user_register.php" class="btn-register">Đăng ký</a></li>
                        <?php endif; ?>
                        
                        
                    </ul>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <div class="container-wrapper-flash">
                <div class="container">
                     <?php display_flash_messages(); ?>
                </div>
            </div>

            <?php if (!$is_profile_page && !$is_invoice_page): ?>
            <div class="container">
            <?php endif; ?>