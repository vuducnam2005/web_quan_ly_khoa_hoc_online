<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/admin_header.php';

$user_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($user_id <= 0) {
    add_flash_message("ID người dùng không hợp lệ.", 'error');
    header("Location: users.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$raw_avatar = $user['avatar'];
$display_avatar = 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; // Ảnh mặc định

if (!empty($raw_avatar)) {
    // 1. Nếu link bắt đầu bằng http hoặc https -> Là link online -> Giữ nguyên
    if (strpos($raw_avatar, 'http') === 0) {
        $display_avatar = $raw_avatar;
    }
    // 2. Ngược lại kiểm tra file trong máy
    elseif (file_exists(__DIR__ . '/../' . $raw_avatar)) {
        $display_avatar = '../' . $raw_avatar; // Thêm ../ để lùi ra thư mục gốc
    }
}
if (!$user) {
    add_flash_message("Không tìm thấy người dùng.", 'error');
    header("Location: users.php");
    exit;
}

$stmt_history = $pdo->prepare("SELECT r.*, c.name as course_name, c.fee, c.course_code FROM registrations r JOIN courses c ON r.course_id = c.id WHERE r.student_email = ? ORDER BY r.registered_at DESC");
$stmt_history->execute([$user['email']]);
$history = $stmt_history->fetchAll();

$total_spent = 0;
foreach ($history as $item) {
    if ($item['status'] === 'Đã xác nhận')
        $total_spent += $item['fee'];
}
?>

<div class="container" style="padding-top: 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1>Chi tiết Học viên</h1>
        <div style="display: flex; gap: 10px;">
            <a href="users.php?action=edit&id=<?php echo $user_id; ?>" class="btn btn-primary">Sửa</a>

            <form action="user_action.php" method="POST"
                onsubmit="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn XÓA người dùng này? Mọi dữ liệu liên quan (đơn hàng, bình luận) sẽ bị xóa theo.');"
                style="margin:0;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo $user_id; ?>">
                <button style="height: 50px; " type="submit" class="btn btn-danger">Xóa User</button>
            </form>

            <a href="users.php" class="btn btn-secondary">« Quay lại</a>
        </div>
    </div>

    <div class="user-detail-grid"
        style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div class="stat-card" style="display: block;">
            <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1rem;">
                <img src="<?php echo h($display_avatar); ?>" alt="Avatar"
                    style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #eee;">
                <div>
                    <h2 style="margin: 0; font-size: 1.5rem;"><?php echo h($user['full_name']); ?></h2>
                    <span style="color: #777;"><?php echo h($user['student_code'] ?? 'Chưa có mã'); ?></span>
                </div>
            </div>
            <div class="info-row" style="margin-bottom: 0.5rem;"><strong>Email:</strong>
                <?php echo h($user['email']); ?></div>
            <div class="info-row" style="margin-bottom: 0.5rem;"><strong>SĐT:</strong>
                <?php echo h($user['phone'] ?? 'Chưa cập nhật'); ?></div>
            <div class="info-row" style="margin-bottom: 0.5rem;"><strong>Ngày tham gia:</strong>
                <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></div>
            <div class="info-row">
                <strong>Trạng thái:</strong>
                <?php if ($user['status'] === 'active'): ?>
                    <span class="status-badge"
                        style="background: var(--success-color); color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">Hoạt
                        động</span>
                <?php else: ?>
                    <span class="status-badge"
                        style="background: var(--danger-color); color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">Đã
                        khóa</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card"
            style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
            <h3 style="color: var(--secondary-color); font-size: 1.2rem;">Tổng tiền đã chi</h3>
            <p style="font-size: 3rem; font-weight: 700; color: var(--success-color); margin: 0.5rem 0;">
                <?php echo number_format($total_spent, 0, ',', '.'); ?> <span
                    style="font-size: 1.5rem; color: #777;">VNĐ</span>
            </p>
            <span style="color: #777;">(Tính trên <?php echo count($history); ?> khóa học đã đăng ký)</span>
        </div>
    </div>

    <h2 class="section-title" style="text-align: left; border-bottom: 2px solid #eee; padding-bottom: 10px;">Lịch sử
        Đăng ký Khóa học</h2>
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khóa học</th>
                    <th>Học phí</th>
                    <th>Ngày đăng ký</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">Học viên này chưa đăng ký khóa học nào.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($history as $item): ?>
                        <tr>
                            <td><strong><?php echo h($item['invoice_code']); ?></strong></td>
                            <td><?php echo h($item['course_name']); ?><br><small
                                    style="color: #777;"><?php echo h($item['course_code']); ?></small></td>
                            <td><?php echo number_format($item['fee'], 0, ',', '.'); ?> VNĐ</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($item['registered_at'])); ?></td>
                            <td>
                                <?php
                                $status_color = '#6c757d';
                                if ($item['status'] === 'Đã xác nhận')
                                    $status_color = 'var(--success-color)';
                                if ($item['status'] === 'Đã hủy')
                                    $status_color = 'var(--danger-color)';
                                ?>
                                <span
                                    style="background-color: <?php echo $status_color; ?>; color: #fff; padding: 4px 8px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                    <?php echo h($item['status']); ?>
                                </span>
                            </td>
                            <td><a href="registrations.php?search=<?php echo h($item['invoice_code']); ?>"
                                    class="btn btn-small">Xem & Xử lý</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>