<?php
require_once __DIR__ . '/../inc/auth.php'; // Kiểm tra đăng nhập
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/admin_header.php';

// === Lấy dữ liệu mới cho Dashboard ===
try {
    // 1. Query lấy 4 thẻ thống kê
    $stmt_stats = $pdo->query("
        SELECT
            (SELECT COUNT(id) FROM courses) as total_courses,
            (SELECT COUNT(id) FROM registrations WHERE status = 'Đã xác nhận') as confirmed_regs,
            (SELECT COUNT(id) FROM registrations WHERE status = 'Đang xử lý') as pending_regs,
            (SELECT SUM(c.fee) FROM registrations r JOIN courses c ON r.course_id = c.id WHERE r.status = 'Đã xác nhận') as total_revenue
    ");
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

    // Gán biến (với giá trị mặc định 0 nếu CSDL trống)
    $total_courses = $stats['total_courses'] ?? 0;
    $confirmed_regs = $stats['confirmed_regs'] ?? 0;
    $pending_regs = $stats['pending_regs'] ?? 0;
    $total_revenue = (float)($stats['total_revenue'] ?? 0);

    // 2. Query lấy 5 hoạt động mới nhất
    $stmt_recent = $pdo->query("
        SELECT r.student_name, r.registered_at, c.name as course_name
        FROM registrations r
        JOIN courses c ON r.course_id = c.id
        ORDER BY r.registered_at DESC
        LIMIT 5
    ");
    $recent_registrations = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='flash-message error'>Lỗi CSDL: " . h($e->getMessage()) . "</div>";
    $total_courses = $confirmed_regs = $pending_regs = $total_revenue = 'N/A';
    $recent_registrations = [];
}
?>

<h1>Chào mừng, <?php echo h($_SESSION['admin_username']); ?>!</h1>
<p>Đây là khu vực quản trị. Bạn có thể quản lý khóa học và các đơn đăng ký tại đây.</p>

<h2 class="section-title" style="text-align: left;">Thống kê nhanh</h2>

<div class="dashboard-stats" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
    
    <div class="stat-card">
        <div class="stat-card-icon" style="color: var(--success-color); background-color: #e3f9e8;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                <path d="M4 3.06h8C13.13 3.06 14 3.93 14 5v6c0 1.07-.87 1.94-1.94 1.94H4C2.87 12.94 2 12.07 2 11V5c0-1.07.87-1.94 1.94-1.94zM3 5v6c0 .55.45 1 1 1h8c.55 0 1-.45 1-1V5c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1z"/>
                <path d="M2 3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3zM1 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zM13 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
            </svg>
        </div>
        <div class="stat-card-content">
            <h3>Doanh thu (Đã xác nhận)</h3>
            <p><?php echo number_format($total_revenue, 0, ',', '.'); ?> VNĐ</p>
            <a href="stats.php">Xem chi tiết</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="color: var(--primary-color); background-color: #e0f2ff;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332C3.154 12.014 3.001 12.754 3 13z"/>
            </svg>
        </div>
        <div class="stat-card-content">
            <h3>Đơn (Đã xác nhận)</h3>
            <p><?php echo $confirmed_regs; ?></p>
            <a href="registrations.php?status=Đã+xác+nhận">Quản lý</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="color: #ffc107; background-color: #fff8e1;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>
            </svg>
        </div>
        <div class="stat-card-content">
            <h3>Đăng ký (Chờ xử lý)</h3>
            <p><?php echo $pending_regs; ?></p>
            <a href="registrations.php?status=Đang+xử+lý">Xem ngay</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="color: #673ab7; background-color: #ede7f6;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zM4 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                <path d="M4.268 3.05A.5.5 0 0 0 3.75 3.5v9a.5.5 0 0 0 .75.433l7.5-4.5a.5.5 0 0 0 0-.866l-7.5-4.5a.5.5 0 0 0-.732-.016z"/>
            </svg>
        </div>
        <div class="stat-card-content">
            <h3>Tổng số khóa học</h3>
            <p><?php echo $total_courses; ?></p>
            <a href="courses.php">Quản lý</a>
        </div>
    </div>
</div>

<h2 class="section-title" style="text-align: left; margin-top: 2rem;">Hoạt động Mới nhất</h2>

<div class="dashboard-recent-activity">
    <h3>5 Đơn đăng ký Gần đây</h3>
    <?php if (empty($recent_registrations)): ?>
        <p>Chưa có đơn đăng ký nào.</p>
    <?php else: ?>
        <ul class="recent-activity-list">
            <?php foreach ($recent_registrations as $reg): ?>
                <li>
                    <div class="activity-info">
                        <strong><?php echo h($reg['student_name']); ?></strong>
                        đã đăng ký khóa học "<?php echo h($reg['course_name']); ?>"
                    </div>
                    <div class="activity-time">
                        <?php echo date('d/m/Y H:i', strtotime($reg['registered_at'])); ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="registrations.php" class="btn btn-secondary" style="margin-top: 1rem; border-radius: 5px;">Xem tất cả đăng ký</a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>