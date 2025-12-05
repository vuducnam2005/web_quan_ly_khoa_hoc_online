<?php
// Session đã được start bởi auth.php
require_once __DIR__ . '/../inc/functions.php';

// (MỚI) Tự động phát hiện trang hiện tại để làm active menu
$current_page = basename($_SERVER['SCRIPT_NAME']); // Lấy tên file, ví dụ: "dashboard.php"
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản trị</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="admin-layout">

        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">Khóa Học - Admin</a>
            </div>

            <h3 class="menu-title">MENU</h3>

            <nav class="admin-nav">
                <ul>
                    <li>
                        <a href="dashboard.php"
                            class="<?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M4 11H2V5h2zM8 11H6V2h2zM12 11h-2V8h2zM14 1.5H2a.5.5 0 0 0-.5.5v12a.5.5 0 0 0 .5.5h12a.5.5 0 0 0 .5-.5v-12a.5.5 0 0 0-.5-.5M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z" />
                            </svg>
                            <span>Tổng quan</span>
                        </a>
                    </li>
                    <li>
                        <a href="courses.php" class="<?php echo ($current_page === 'courses.php') ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zM4 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z" />
                                <path
                                    d="M4.268 3.05A.5.5 0 0 0 3.75 3.5v9a.5.5 0 0 0 .75.433l7.5-4.5a.5.5 0 0 0 0-.866l-7.5-4.5a.5.5 0 0 0-.732-.016z" />
                            </svg>
                            <span>Quản lý Khóa học</span>
                        </a>
                    </li>
                    <li>
                        <a href="registrations.php"
                            class="<?php echo ($current_page === 'registrations.php') ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M2 2.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H2.5a.5.5 0 0 1-.5-.5zM2 6.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H2.5a.5.5 0 0 1-.5-.5zM2 10.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H2.5a.5.5 0 0 1-.5-.5z" />
                            </svg>
                            <span>Quản lý Đăng ký</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="<?php echo ($current_page === 'users.php') ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M15 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1zm-7.978-1L7 12.996c.001-.246.154-.986.832-1.664C8.516 10.68 9.789 10 12 10s3.484.68 4.168 1.332c.678.678.83 1.418.832 1.664zM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.5 4.5 0 0 1-3.328-1.428A5.5 5.5 0 0 0 3 13.5zM1 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1z" />
                            </svg>
                            <span>Quản lý User</span>
                        </a>
                    </li>
                    <li>
                        <a href="top_spenders.php"
                            class="<?php echo ($current_page === 'top_spenders.php') ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5c0 .538-.012 1.05-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.777-2.833 3.011v2.173l1.425.356c.194.048.377.135.537.255L13.3 15.1a.5.5 0 0 1-.3.9H3a.5.5 0 0 1-.3-.9l1.838-1.379c.16-.12.343-.207.537-.255L6.5 13.11v-2.173c-.955-.234-2.043-1.146-2.833-3.012a3 3 0 1 1-1.132-5.89A33.076 33.076 0 0 1 2.5.5zm.099 2.54a2 2 0 0 0 .72 3.935c-.333-1.05-.588-2.346-.72-3.935zm10.083 3.935a2 2 0 0 0 .72-3.935c-.133 1.59-.388 2.885-.72 3.935zM3.504 1c.007.517.026 1.006.056 1.469.13 2.028.457 3.546.87 4.667C5.294 9.48 6.484 10 7 10a.5.5 0 0 1 .5.5v2.61a1 1 0 0 1-.757.97l-1.426.356a.5.5 0 0 0-.179.085L4.5 15h7l-.638-.479a.501.501 0 0 0-.18-.085l-1.425-.356a1 1 0 0 1-.757-.97V10.5A.5.5 0 0 1 9 10c.516 0 1.706-.52 2.57-2.864.413-1.12.74-2.64.87-4.667.03-.463.049-.952.056-1.469H3.504z" />
                            </svg>
                            <span>Top Chi tiêu</span>
                        </a>
                    </li>
                    <li>
                        <a href="chat_users.php"
                            class="<?php echo ($current_page === 'chat_users.php') ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z" />
                            </svg>
                            <span>Phản hồi học viên</span>
                        </a>
                    </li>
                    <li>
                        <a href="stats.php" class="<?php echo ($current_page === 'stats.php') ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1zM2 1v12h2V2zm4 0v5h2V1zm4 0v10h2V1z" />
                            </svg>
                            <span>Thống kê</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <a href="../index.php" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                        viewBox="0 0 16 16">
                        <path
                            d="M8.5 10.5H5a.5.5 0 0 1 0-1h3.5a.5.5 0 0 1 0 1M8.5 8.5H5a.5.5 0 0 1 0-1h3.5a.5.5 0 0 1 0 1m3.5 0.5a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 1 .5-.5h.5a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5zM2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1" />
                    </svg>
                    <span>Xem Trang</span>
                </a>
                <li>
                    <a href="../user_login.php" target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.646-1.646a.5.5 0 0 1 .708 0l2.5 2.5a.5.5 0 0 1-.708.708L8.5 6.207V11a.5.5 0 0 1-1 0V6.207L5.054 8.562a.5.5 0 0 1-.708-.708z" />
                            <path
                                d="M8.5 10.5H5a.5.5 0 0 1 0-1h3.5a.5.5 0 0 1 0 1M8.5 8.5H5a.5.5 0 0 1 0-1h3.5a.5.5 0 0 1 0 1" />
                        </svg>
                        <span>Đăng nhập User </span>
                    </a>
                </li>
                <a href="logout.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                        viewBox="0 0 16 16">
                        <path fill-rule="evenodd"
                            d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                        <path fill-rule="evenodd"
                            d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                    </svg>
                    <span>(<?php echo h($_SESSION['admin_username'] ?? 'Admin'); ?>) Đăng xuất</span>
                </a>
            </div>
        </aside>

        <main class="admin-main-content">
            <div class="container">
                <?php display_flash_messages(); // Hiển thị thông báo (nếu có) ?>