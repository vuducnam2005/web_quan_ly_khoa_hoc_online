<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$course = null;
$page_title = "Quản lý Khóa học";

// Lấy danh sách danh mục (để hiển thị trong dropdown)
try {
    $stmt_cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt_cats->fetchAll();
} catch (PDOException $e) { $categories = []; }

$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// --- LOGIC XỬ LÝ FORM SỬA/THÊM ---
if (($action === 'edit' || $action === 'add') && !empty($old_input)) {
    $course = $old_input; 
} elseif ($action === 'edit' && $id > 0) {
    $page_title = "Sửa Khóa học";
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    $course = $stmt->fetch();
    if (!$course) {
        add_flash_message("Không tìm thấy khóa học!", 'error');
        header("Location: courses.php");
        exit;
    }
} elseif ($action === 'add') {
    $page_title = "Thêm Khóa học mới";
} else {
    // === LOGIC LỌC & PHÂN TRANG ===
    $filter_cat = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
    $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $per_page = 10; 
    $offset = ($page - 1) * $per_page;

    $where_sql = "";
    $params = [];
    
    if ($filter_cat > 0) {
        $where_sql = "WHERE c.category_id = ?";
        $params[] = $filter_cat;
    }

    // Đếm tổng số
    $count_sql = "SELECT COUNT(c.id) FROM courses c $where_sql";
    $stmt_count = $pdo->prepare($count_sql);
    $stmt_count->execute($params);
    $total_courses = $stmt_count->fetchColumn();
    $total_pages = ceil($total_courses / $per_page);

    // Lấy dữ liệu
    $data_sql = "
        SELECT c.*, cat.name as category_name 
        FROM courses c
        LEFT JOIN categories cat ON c.category_id = cat.id
        $where_sql
        ORDER BY c.id ASC 
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($data_sql);
    $param_index = 1;
    foreach ($params as $val) {
        $stmt->bindValue($param_index, $val);
        $param_index++;
    }
    $stmt->bindValue($param_index, $per_page, PDO::PARAM_INT);
    $param_index++;
    $stmt->bindValue($param_index, $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $courses = $stmt->fetchAll();
    
    $query_string = $filter_cat > 0 ? "&category_id=$filter_cat" : "";
}

require_once __DIR__ . '/../inc/admin_header.php';
?>

<h1><?php echo h($page_title); ?></h1>

<?php
if (!empty($errors)) {
    echo '<div class="flash-message error">';
    echo '<strong>Vui lòng sửa các lỗi sau:</strong><ul>';
    foreach ($errors as $error) {
        echo '<li>' . h($error) . '</li>';
    }
    echo '</ul></div>';
}
?>

<?php if ($action === 'list'): ?>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <a href="courses.php?action=add" class="btn btn-primary">Thêm Khóa học mới</a>
    </div>

    <form action="courses.php" method="GET" class="filter-form" style="margin-bottom: 1.5rem; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 10px;">
            <label for="filter_cat" style="margin: 0; font-weight: bold;">Lọc theo danh mục:</label>
            <select name="category_id" id="filter_cat" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
                <option value="0">-- Tất cả --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($filter_cat == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo h($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-small btn-primary">Lọc</button>
            <?php if($filter_cat > 0): ?>
                <a href="courses.php" class="btn btn-small btn-secondary">Bỏ lọc</a>
            <?php endif; ?>
        </div>
    </form>
    
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã KH</th>
                    <th>Ảnh</th>
                    <th>Tên khóa học</th>
                    <th>Danh mục</th>
                    <th>Học phí</th>
                    <th>File</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">Không có khóa học nào.</td>
                    </tr>
                <?php else: ?>
                    <?php $stt = ($page - 1) * $per_page + 1; ?>
                    <?php foreach ($courses as $c): ?>
                    <tr>
                        <td data-label="STT"><?php echo $stt; ?></td>
                        <td data-label="Mã KH"><strong><?php echo h($c['course_code']); ?></strong></td>
                        <td data-label="Ảnh">
                            <?php if (!empty($c['image_url'])): ?>
                                <img src="../<?php echo h($c['image_url']); ?>" alt="Ảnh" class="admin-table-thumbnail">
                            <?php else: ?>
                                <span>(Chưa có)</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Tên khóa học"><?php echo h($c['name']); ?></td>
                        <td data-label="Danh mục">
                            <span style="background: #e3f2fd; color: #0d47a1; padding: 2px 6px; border-radius: 4px; font-size: 0.85rem;">
                                <?php echo h($c['category_name'] ?? 'Chưa phân loại'); ?>
                            </span>
                        </td>
                        <td data-label="Học phí"><?php echo number_format(h($c['fee']), 0, ',', '.'); ?> VNĐ</td>
                        
                        <td data-label="File">
                            <?php if (!empty($c['material_url']) && file_exists(__DIR__ . '/../' . $c['material_url'])): ?>
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <span style="color: var(--success-color); font-weight: bold; font-size: 1.2rem;" title="Đã có file">✅</span>
                                    <form action="course_action.php" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa file tài liệu này?');" style="margin:0;">
                                        <input type="hidden" name="action" value="delete_material">
                                        <input type="hidden" name="id" value="<?php echo h($c['id']); ?>">
                                        <button type="submit" class="btn btn-small btn-danger" style="padding: 2px 6px; font-size: 0.8rem; border-radius: 4px;" title="Xóa file">🗑</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span style="color: var(--danger-color); font-weight: bold; font-size: 1.2rem;" title="Chưa có file">❌</span>
                            <?php endif; ?>
                        </td>

                        <td data-label="Hành động">
                            <div class="action-buttons">
                                <a href="courses.php?action=edit&id=<?php echo h($c['id']); ?>" class="btn btn-small">Sửa</a>
                                <form action="course_action.php" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khóa học này?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo h($c['id']); ?>">
                                    <button type="submit" class="btn btn-small btn-danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php $stt++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="courses.php?page=<?php echo $i . $query_string; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <form action="course_action.php" method="POST" class="admin-form" enctype="multipart/form-data">
        
        <input type="hidden" name="action" value="<?php echo ($action === 'edit') ? 'update' : 'create'; ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo h($id); ?>">
            <div class="form-group">
                <label>Mã Khóa Học (Không thể thay đổi)</label>
                <input type="text" value="<?php echo h($course['course_code'] ?? ''); ?>" disabled>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="category_id">Danh mục khóa học *</label>
            <select name="category_id" id="category_id">
                <option value="">-- Chọn danh mục --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo h($cat['id']); ?>" 
                        <?php echo (($course['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo h($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="name">Tên khóa học *</label>
            <input type="text" id="name" name="name" value="<?php echo h($course['name'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="fee">Học phí (VNĐ) *</label>
            <input type="number" id="fee" name="fee" value="<?php echo h($course['fee'] ?? '0'); ?>" step="1000">
        </div>

        <div class="form-group">
            <label for="duration">Thời lượng *</label>
            <input type="text" id="duration" name="duration" value="<?php echo h($course['duration'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="course_image">Ảnh minh họa (Tải lên từ máy)</label>
            <?php if ($action === 'edit' && !empty($course['image_url'])): ?>
                <div class="current-image">
                    <p>Ảnh hiện tại:</p>
                    <img src="../<?php echo h($course['image_url']); ?>" alt="Ảnh hiện tại" style="max-width: 200px;">
                </div>
            <?php endif; ?>
            <input type="file" id="course_image" name="course_image">
            <small>Chỉ chấp nhận file .jpg, .png, .gif (dưới 2MB).</small>
        </div>

        <div class="form-group">
            <label for="course_material">Tài liệu khóa học (File ZIP/PDF)</label>
            <?php if (!empty($course['material_url']) && file_exists(__DIR__ . '/../' . $course['material_url'])): ?>
                <p style="color:green; font-weight:bold; font-size:0.9rem;">
                    ✓ Đang có file: <a href="../<?php echo h($course['material_url']); ?>" target="_blank">Tải về kiểm tra</a>
                </p>
            <?php endif; ?>
            <input type="file" id="course_material" name="course_material">
            <small>Hỗ trợ file .zip, .rar, .pdf (Max 50MB). Tải file mới sẽ ghi đè file cũ.</small>
        </div>

        <div class="form-group">
            <label for="short_description">Mô tả ngắn *</label>
            <textarea id="short_description" name="short_description" rows="3"><?php echo h($course['short_description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="full_description">Mô tả đầy đủ * (Chấp nhận HTML)</label>
            <textarea id="full_description" name="full_description" rows="10"><?php echo h($course['full_description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="what_you_learn">Bạn sẽ học được gì? (Mỗi ý một dòng)</label>
            <textarea id="what_you_learn" name="what_you_learn" rows="6"><?php echo h($course['what_you_learn'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="requirements">Yêu cầu khóa học (Mỗi ý một dòng)</label>
            <textarea id="requirements" name="requirements" rows="4"><?php echo h($course['requirements'] ?? ''); ?></textarea>
        </div>

        <div class="form-group form-actions">
            <button type="submit" class="btn btn-primary">
                <?php echo ($action === 'edit') ? 'Cập nhật' : 'Tạo mới'; ?>
            </button>
            <a href="courses.php" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
<?php endif; ?>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>