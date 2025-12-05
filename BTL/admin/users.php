<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/admin_header.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = null;
$page_title = "Quản lý Người dùng";

$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// Xử lý logic cho Form Sửa/Thêm (Giữ nguyên)
if (($action === 'edit' || $action === 'add') && !empty($old_input)) {
    $user = $old_input;
} elseif ($action === 'edit' && $id > 0) {
    $page_title = "Sửa Người dùng";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) { header("Location: users.php"); exit; }
} elseif ($action === 'add') {
    $page_title = "Thêm Người dùng mới";
} else {
    // === (MỚI) LOGIC TÌM KIẾM VÀ LỌC ===
    
    // 1. Lấy tham số từ URL
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
    
    // 2. Xây dựng câu truy vấn động
    $sql = "SELECT * FROM users";
    $where = [];
    $params = [];

    // Nếu có tìm kiếm (Tên hoặc Email)
    if (!empty($search)) {
        $where[] = "(full_name LIKE ? OR email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Nếu có lọc theo trạng thái
    if (!empty($filter_status)) {
        $where[] = "status = ?";
        $params[] = $filter_status;
    }

    // Ghép các điều kiện lại
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY created_at DESC";

    // 3. Thực thi truy vấn
    $stmt_users = $pdo->prepare($sql);
    $stmt_users->execute($params);
    $users = $stmt_users->fetchAll();
}
?>

<h1><?php echo h($page_title); ?></h1>

<?php
if (!empty($errors)) {
    echo '<div class="flash-message error">';
    foreach ($errors as $error) echo '<li>' . h($error) . '</li>';
    echo '</div>';
}
?>

<?php if ($action === 'list'): ?>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <a href="users.php?action=add" class="btn btn-primary">Thêm Người dùng mới</a>
    </div>

    <form action="users.php" method="GET" class="filter-form" style="margin-bottom: 2rem;">
        <div class="form-group">
            <label for="search">Tìm kiếm (Tên, Email)</label>
            <input type="text" id="search" name="search" value="<?php echo h($search); ?>" placeholder="Nhập tên hoặc email...">
        </div>
        
        <div class="form-group">
            <label for="status">Trạng thái</label>
            <select name="status" id="status">
                <option value="">-- Tất cả --</option>
                <option value="active" <?php echo ($filter_status === 'active') ? 'selected' : ''; ?>>Hoạt động</option>
                <option value="locked" <?php echo ($filter_status === 'locked') ? 'selected' : ''; ?>>Đã khóa</option>
            </select>
        </div>

        <div class="form-group form-actions">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            <a href="users.php" class="btn btn-secondary">Bỏ lọc</a>
        </div>
    </form>
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th>Mật khẩu</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" style="text-align: center;">Không tìm thấy người dùng nào phù hợp.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td data-label="ID"><?php echo h($u['student_code'] ?? $u['id']); ?></td>
                        <td data-label="Họ và tên"><strong><?php echo h($u['full_name']); ?></strong></td>
                        <td data-label="Email"><?php echo h($u['email']); ?></td>
                        <td data-label="SĐT"><?php echo h($u['phone'] ?? '-'); ?></td>
                        <td data-label="Mật khẩu" style="color: var(--danger-color); font-family: monospace;"><?php echo h($u['password_hash']); ?></td>
                        <td data-label="Trạng thái">
                            <?php if ($u['status'] === 'active'): ?>
                                <span style="color: var(--success-color); font-weight:bold; background: #e6fffa; padding: 2px 8px; border-radius: 4px;">Hoạt động</span>
                            <?php else: ?>
                                <span style="color: var(--danger-color); font-weight:bold; background: #fff5f5; padding: 2px 8px; border-radius: 4px;">Đã khóa</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Hành động">
                            <div class="action-buttons" style="gap: 5px; flex-wrap: wrap;">
                                <a href="user_detail.php?id=<?php echo h($u['id']); ?>" class="btn btn-small" style="background-color: #17a2b8; color: white;">Chi tiết</a>
                                <a href="users.php?action=edit&id=<?php echo h($u['id']); ?>" class="btn btn-small">Sửa</a>
                                
                                <form action="user_action.php" method="POST" style="margin:0;">
                                    <input type="hidden" name="action" value="toggle_lock">
                                    <input type="hidden" name="id" value="<?php echo h($u['id']); ?>">
                                    <input type="hidden" name="status" value="<?php echo $u['status']; ?>">
                                    <?php if ($u['status'] === 'active'): ?>
                                        <button type="submit" class="btn btn-small" style="background-color: #ffc107; color: #000; border:none;">Khóa</button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-small" style="background-color: #28a745; color: white; border:none;">Mở</button>
                                    <?php endif; ?>
                                </form>
                                
                                <form action="user_action.php" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn XÓA vĩnh viễn người dùng này?');" style="margin:0;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo h($u['id']); ?>">
                                    <button type="submit" class="btn btn-small btn-danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <form action="user_action.php" method="POST" class="admin-form">
        <input type="hidden" name="action" value="<?php echo ($action === 'edit') ? 'update' : 'create'; ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo h($id); ?>">
            <div class="form-group">
                <label>Mã Học Viên</label>
                <input type="text" value="<?php echo h($user['student_code'] ?? ''); ?>" disabled>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label>Họ và tên *</label>
            <input type="text" name="full_name" value="<?php echo h($user['full_name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" value="<?php echo h($user['email'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Mật khẩu <?php echo ($action === 'create') ? '*' : ''; ?></label>
            <input type="text" name="password" placeholder="<?php echo ($action === 'edit') ? 'Nhập nếu muốn đổi' : ''; ?>">
        </div>
        <?php if ($action === 'edit'): ?>
        <div class="form-group">
            <label>Trạng thái</label>
            <select name="status">
                <option value="active" <?php echo (($user['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Hoạt động</option>
                <option value="locked" <?php echo (($user['status'] ?? 'active') === 'locked') ? 'selected' : ''; ?>>Đã khóa</option>
            </select>
        </div>
        <?php endif; ?>
        <div class="form-group form-actions">
            <button type="submit" class="btn btn-primary"><?php echo ($action === 'edit') ? 'Cập nhật' : 'Tạo mới'; ?></button>
            <a href="users.php" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
<?php endif; ?>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>