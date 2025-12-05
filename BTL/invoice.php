<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header("Location: user_login.php"); exit; }

require 'inc/db.php';
require 'inc/functions.php';

$user_id = (int)$_SESSION['user_id'];
$reg_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// (MỚI) Lấy mã học viên của user đang đăng nhập
$stmt_u = $pdo->prepare("SELECT student_code FROM users WHERE id = ?");
$stmt_u->execute([$user_id]);
$my_student_code = $stmt_u->fetchColumn();

// (MỚI) Kiểm tra hóa đơn dựa trên student_code
$stmt = $pdo->prepare("
    SELECT 
        r.id as reg_id, r.registered_at, r.status, r.invoice_code,
        c.name as course_name, c.fee, c.course_code, c.duration,
        u.full_name as user_name, u.email as user_email, u.phone as user_phone
    FROM registrations r
    JOIN courses c ON r.course_id = c.id
    JOIN users u ON r.student_code = u.student_code 
    WHERE 
        r.id = ? AND r.student_code = ? 
");
$stmt->execute([$reg_id, $my_student_code]);
$invoice = $stmt->fetch();

if (!$invoice) {
    add_flash_message("Không tìm thấy hóa đơn.", 'error');
    header("Location: profile.php"); exit;
}

// ... (Phần hiển thị HTML bên dưới giữ nguyên) ...
// ...
$status = $invoice['status'];
$show_invoice = ($status === 'Đã xác nhận');
$modal_message = "";
$modal_title = "Thông báo";
if ($status === 'Đang xử lý') {
    $modal_title = "Chờ xác nhận";
    $modal_message = "Đơn hàng đang chờ duyệt.";
} elseif ($status === 'Đã hủy') {
    $modal_title = "Đã bị hủy";
    $modal_message = "Đơn hàng đã bị hủy.";
}

require 'inc/header.php';
?>
<div class="invoice-layout">
    <?php if ($show_invoice): ?>
    <div class="invoice-box">
        <div class="invoice-header">
            <div class="header-left">
                <h1 class="logo">Khóa Học Online</h1>
                <span>Số 1 ,Phố Xốm , Hà Đông , Hà Nội</span><br><span>online@khoahoc.dev</span>
            </div>
            <div class="header-right">
                <h2>HÓA ĐƠN</h2>
                <span class="invoice-id">#<?php echo h($invoice['invoice_code']); ?></span>
            </div>
        </div>
        <div class="invoice-customer">
            <div class="bill-to">
                <strong>HÓA ĐƠN CHO:</strong><br>
                <?php echo h($invoice['user_name']); ?><br>
                <?php echo h($invoice['user_email']); ?><br>
                <?php echo h($invoice['user_phone'] ?? '(Chưa có SĐT)'); ?>
            </div>
            <div class="invoice-meta">
                <div><strong>Ngày tạo:</strong><span><?php echo date('d/m/Y', strtotime($invoice['registered_at'])); ?></span></div>
                <div><strong>Mã đơn:</strong><span><?php echo h($invoice['invoice_code']); ?></span></div>
                <div><strong>Trạng thái:</strong><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $invoice['status'])); ?>"><?php echo h($invoice['status']); ?></span></div>
            </div>
        </div>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead><tr><th>Mã khóa học</th><th>Tên khóa học</th><th>Thời lượng</th><th>Đơn giá</th></tr></thead>
                <tbody><tr><td><?php echo h($invoice['course_code']); ?></td><td><?php echo h($invoice['course_name']); ?></td><td><?php echo h($invoice['duration']); ?></td><td><?php echo number_format($invoice['fee'], 0, ',', '.'); ?> VNĐ</td></tr></tbody>
                <tfoot><tr><td colspan="3" class="total-label">Tổng cộng (VNĐ)</td><td class="total-value"><?php echo number_format($invoice['fee'], 0, ',', '.'); ?> VNĐ</td></tr></tfoot>
            </table>
        </div>
        <div class="invoice-footer">
            <p>Cảm ơn bạn đã tin tưởng!</p>
            <div class="invoice-actions"><a href="profile.php?tab=courses#courses" class="btn btn-secondary">&laquo; Quay lại</a><button onclick="window.print();" class="btn btn-primary">In hóa đơn</button></div>
        </div>
    </div>
    <?php else: ?>
    <div class="modal-overlay" id="myModal" style="display: flex;"><div class="modal-box error"><h3><?php echo h($modal_title); ?></h3><p><?php echo h($modal_message); ?></p><a href="profile.php?tab=courses#courses" class="btn-close">Đóng</a></div></div>
    <?php endif; ?>
</div>
<?php require 'inc/footer.php'; ?>