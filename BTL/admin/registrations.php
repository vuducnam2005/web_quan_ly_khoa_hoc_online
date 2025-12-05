<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';

// (MỚI) Logic Thống kê Doanh thu (đã thêm ở lần trước)
try {
    $stmt_revenue = $pdo->query("
        SELECT SUM(CASE WHEN r.status = 'Đã xác nhận' THEN c.fee ELSE 0 END) as confirmed_revenue
        FROM registrations r
        JOIN courses c ON r.course_id = c.id
    ");
    $total_confirmed_revenue = (float)($stmt_revenue->fetchColumn() ?? 0);
} catch (PDOException $e) {
    $total_confirmed_revenue = 0;
}

require_once __DIR__ . '/../inc/admin_header.php';

// Lấy danh sách khóa học để lọc
$courses_stmt = $pdo->query("SELECT id, name FROM courses");
$courses_list = $courses_stmt->fetchAll(PDO::FETCH_KEY_PAIR); 

// --- Xử lý Lọc và Tìm kiếm ---
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_course = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$possible_statuses = ['Đang xử lý', 'Đã xác nhận', 'Đã hủy'];

$sql_conditions = [];
$params = [];
$query_string = []; 

if ($search_term !== '') {
    // (SỬA) Thêm tìm kiếm theo Mã hóa đơn
    $sql_conditions[] = "(r.student_name LIKE ? OR r.student_email LIKE ? OR r.invoice_code LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $query_string['search'] = $search_term;
}
if ($filter_course > 0) {
    $sql_conditions[] = "r.course_id = ?";
    $params[] = $filter_course;
    $query_string['course_id'] = $filter_course;
}
if (in_array($filter_status, $possible_statuses)) {
    $sql_conditions[] = "r.status = ?";
    $params[] = $filter_status;
    $query_string['status'] = $filter_status;
}

$base_sql = "FROM registrations r JOIN courses c ON r.course_id = c.id";
$where_sql = "";
if (!empty($sql_conditions)) {
    $where_sql = " WHERE " . implode(" AND ", $sql_conditions);
}

// --- Logic Phân trang ---
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;
$http_query = http_build_query($query_string); 

// Đếm tổng số bản ghi
$total_count_sql = "SELECT COUNT(r.id) " . $base_sql . $where_sql;
$total_count_stmt = $pdo->prepare($total_count_sql);
$total_count_stmt->execute($params);
$total_regs = $total_count_stmt->fetchColumn();
$total_pages = ceil($total_regs / $per_page);

// Lấy dữ liệu
$data_sql = "SELECT r.*, c.name as course_name 
             " . $base_sql . $where_sql . " 
             ORDER BY r.registered_at DESC 
             LIMIT ? OFFSET ?"; 

$stmt = $pdo->prepare($data_sql);
$param_index = 1;
foreach ($params as $value) {
    $stmt->bindValue($param_index, $value);
    $param_index++;
}
$stmt->bindValue($param_index, $per_page, PDO::PARAM_INT);
$param_index++;
$stmt->bindValue($param_index, $offset, PDO::PARAM_INT);

$stmt->execute();
$registrations = $stmt->fetchAll();
?>

<h1>Quản lý Đăng ký</h1>

<div class="dashboard-stats" style="grid-template-columns: 1fr; margin-bottom: 1.5rem;">
    <div class="stat-card">
        <h3>Tổng Doanh thu (Đã xác nhận)</h3>
        <p style="color: var(--success-color);">
            <?php echo number_format($total_confirmed_revenue, 0, ',', '.'); ?> VNĐ
        </p>
        <span class="stat-detail">Con số này sẽ cập nhật mỗi khi bạn duyệt đơn.</span>
    </div>
</div>


<form action="registrations.php" method="GET" class="filter-form">
    <div class="form-group">
        <label for="search">Tìm kiếm (Tên, Email, Mã đơn)</label>
        <input type="text" id="search" name="search" value="<?php echo h($search_term); ?>">
    </div>
    <div class="form-group">
        <label for="course_id">Lọc theo Khóa học</label>
        <select id="course_id" name="course_id">
            <option value="">-- Tất cả khóa học --</option>
            <?php foreach ($courses_list as $id => $name): ?>
                <option value="<?php echo h($id); ?>" <?php echo ($id == $filter_course) ? 'selected' : ''; ?>>
                    <?php echo h($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="status">Lọc theo Trạng thái</label>
        <select id="status" name="status">
            <option value="">-- Tất cả trạng thái --</option>
            <?php foreach ($possible_statuses as $status): ?>
                <option value="<?php echo h($status); ?>" <?php echo ($status === $filter_status) ? 'selected' : ''; ?>>
                    <?php echo h($status); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group form-actions">
        <button type="submit" class="btn btn-primary">Lọc</button>
        <a href="registrations.php" class="btn btn-secondary">Reset</a>
    </div>
</form>

<div class="admin-table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Mã Đơn</th> <th>Học viên</th>
                <th>Thông tin liên hệ</th>
                <th>Khóa học</th>
                <th>Ngày ĐK</th>
                <th>Trạng thái (Khóa)</th>
                <th>Hành động (Xóa)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($registrations)): ?>
                <tr>
                    <td colspan="7">Không tìm thấy đăng ký nào khớp.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($registrations as $reg): ?>
                <tr>
                    <td data-label="Mã Đơn" style="font-weight: bold;"><?php echo h($reg['invoice_code']); ?></td>
                    <td data-label="Học viên"><?php echo h($reg['student_name']); ?></td>
                    <td data-label="Liên hệ">
                        <?php echo h($reg['student_email']); ?><br>
                        <?php echo h($reg['student_phone']); ?>
                    </td>
                    <td data-label="Khóa học"><?php echo h($reg['course_name']); ?></td>
                    <td data-label="Ngày ĐK"><?php echo date('d/m/Y H:i', strtotime($reg['registered_at'])); ?></td>
                    <td data-label="Trạng thái (Khóa)">
                        <form action="reg_action.php" method="POST" class="status-update-form">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="id" value="<?php echo h($reg['id']); ?>">
                            <input type="hidden" name="redirect_query" value="<?php echo h(http_build_query($_GET)); ?>">
                            <select name="status">
                                <?php foreach ($possible_statuses as $status): ?>
                                    <option value="<?php echo h($status); ?>" <?php echo ($status === $reg['status']) ? 'selected' : ''; ?>>
                                        <?php echo h($status); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-small">Cập nhật</button>
                        </form>
                    </td>
                    <td data-label="Hành động (Xóa)">
                        <form action="reg_action.php" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn XÓA vĩnh viễn đăng ký này?');">
                            <input type="hidden" name="action" value="delete_registration">
                            <input type="hidden" name="id" value="<?php echo h($reg['id']); ?>">
                            <input type="hidden" name="redirect_query" value="<?php echo h(http_build_query($_GET)); ?>">
                            <button type="submit" class="btn btn-small btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="registrations.php?page=<?php echo $i; ?>&<?php echo $http_query; ?>" 
               class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>