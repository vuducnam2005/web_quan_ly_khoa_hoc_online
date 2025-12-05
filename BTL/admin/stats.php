<?php
require_once __DIR__ . '/../inc/auth.php'; // Kiểm tra đăng nhập
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/admin_header.php';

// === Query 1: Lấy các số liệu tổng quan ===
try {
    // Dùng 1 query để lấy tất cả stat cards
    $stmt_summary = $pdo->query("
        SELECT
            SUM(CASE WHEN r.status = 'Đã xác nhận' THEN c.fee ELSE 0 END) as confirmed_revenue,
            SUM(CASE WHEN r.status = 'Đang xử lý' THEN c.fee ELSE 0 END) as pending_revenue,
            SUM(CASE WHEN r.status = 'Đã xác nhận' THEN 1 ELSE 0 END) as confirmed_count,
            SUM(CASE WHEN r.status = 'Đang xử lý' THEN 1 ELSE 0 END) as pending_count
        FROM
            registrations r
        JOIN
            courses c ON r.course_id = c.id
    ");
    $summary = $stmt_summary->fetch(PDO::FETCH_ASSOC);

    // Gán giá trị, dùng ?? 0 để tránh lỗi nếu CSDL trống
    $total_confirmed_revenue = (float)($summary['confirmed_revenue'] ?? 0);
    $total_pending_revenue = (float)($summary['pending_revenue'] ?? 0);
    $total_confirmed_count = (int)($summary['confirmed_count'] ?? 0);
    $total_pending_count = (int)($summary['pending_count'] ?? 0);

    // === Query 2: Lấy thống kê chi tiết theo từng khóa học ===
    // Chỉ tính các khóa học đã được 'Đã xác nhận'
    $stmt_courses = $pdo->query("
        SELECT
            c.name as course_name,
            COUNT(r.id) as confirmed_regs,
            SUM(c.fee) as confirmed_revenue_per_course
        FROM
            registrations r
        JOIN
            courses c ON r.course_id = c.id
        WHERE
            r.status = 'Đã xác nhận'
        GROUP BY
            c.id, c.name
        ORDER BY
            confirmed_revenue_per_course DESC
    ");
    $courses_stats = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='flash-message error'>Lỗi CSDL: " . h($e->getMessage()) . "</div>";
    // Dừng thực thi nếu có lỗi CSDL
    require_once __DIR__ . '/../inc/admin_footer.php';
    exit;
}
?>

<h1>Thống kê Doanh thu</h1>
<p>Chi tiết doanh thu dựa trên các đơn đăng ký đã được "Đã xác nhận".</p>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Doanh thu (Đã xác nhận)</h3>
        <p style="color: var(--success-color);">
            <?php echo number_format($total_confirmed_revenue, 0, ',', '.'); ?> VNĐ
        </p>
        <span class="stat-detail">Tổng số <?php echo $total_confirmed_count; ?> đơn</span>
    </div>
    <div class="stat-card">
        <h3>Doanh thu (Chờ duyệt)</h3>
        <p style="color: var(--secondary-color);">
            <?php echo number_format($total_pending_revenue, 0, ',', '.'); ?> VNĐ
        </p>
        <span class="stat-detail">Tổng số <?php echo $total_pending_count; ?> đơn</span>
    </div>
    <div class="stat-card">
        <h3>Tổng Đơn (Đã xác nhận)</h3>
        <p><?php echo $total_confirmed_count; ?></p>
        <span class="stat-detail">&nbsp;</span>
    </div>
    <div class="stat-card">
        <h3>Tổng Đơn (Chờ duyệt)</h3>
        <p><?php echo $total_pending_count; ?></p>
        <span class="stat-detail">&nbsp;</span>
    </div>
</div>

<h2 class="section-title" style="text-align: left; margin-top: 2rem;">
    Chi tiết Doanh thu theo Khóa học (Đã xác nhận)
</h2>

<div class="admin-table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Tên khóa học</th>
                <th>Số đơn (Đã xác nhận)</th>
                <th>Doanh thu</th>
                <th>Tỷ lệ Doanh thu</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($courses_stats)): ?>
                <tr>
                    <td colspan="4">Chưa có doanh thu nào được xác nhận.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($courses_stats as $course): ?>
                    <?php
                        // Tính tỷ lệ %
                        $percentage = ($total_confirmed_revenue > 0) 
                                      ? ($course['confirmed_revenue_per_course'] / $total_confirmed_revenue) * 100
                                      : 0;
                    ?>
                    <tr>
                        <td data-label="Khóa học"><?php echo h($course['course_name']); ?></td>
                        <td data-label="Số đơn"><?php echo h($course['confirmed_regs']); ?></td>
                        <td data-label="Doanh thu">
                            <strong><?php echo number_format($course['confirmed_revenue_per_course'], 0, ',', '.'); ?> VNĐ</strong>
                        </td>
                        <td data-label="Tỷ lệ">
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?php echo round($percentage, 2); ?>%;"></div>
                                <span class="progress-label"><?php echo round($percentage, 1); ?>%</span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: var(--light-color);">
                <td><strong>TỔNG CỘNG</strong></td>
                <td><strong><?php echo $total_confirmed_count; ?></strong></td>
                <td><strong><?php echo number_format($total_confirmed_revenue, 0, ',', '.'); ?> VNĐ</strong></td>
                <td><strong>100%</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>