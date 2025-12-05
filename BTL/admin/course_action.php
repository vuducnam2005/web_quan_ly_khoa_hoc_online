<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: courses.php");
    exit;
}

$action = $_POST['action'] ?? '';

// === HÀM XỬ LÝ UPLOAD ẢNH ===
function handle_course_upload(array $file_data, ?string $old_image_path = null): ?string
{
    if (!isset($file_data) || $file_data['error'] !== UPLOAD_ERR_OK) {
        if ($file_data['error'] === UPLOAD_ERR_NO_FILE) {
            return $old_image_path; 
        }
        throw new Exception("Lỗi tải ảnh lên. Mã lỗi: " . $file_data['error']);
    }

    $file = $file_data;
    $upload_dir = __DIR__ . '/../uploads/courses/';
    
    // Tạo thư mục nếu chưa có
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $max_size = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $max_size) {
        throw new Exception("File ảnh quá lớn. Vui lòng chọn file nhỏ hơn 2MB.");
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($file['tmp_name']);
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception("Định dạng ảnh không hợp lệ. Chỉ chấp nhận JPG, PNG, GIF.");
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $unique_filename = 'course_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $target_path = $upload_dir . $unique_filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        if ($old_image_path && file_exists(__DIR__ . '/../' . $old_image_path)) {
            unlink(__DIR__ . '/../' . $old_image_path);
        }
        return 'uploads/courses/' . $unique_filename;
    } else {
        throw new Exception("Không thể di chuyển file ảnh đã tải lên.");
    }
}

// === HÀM XỬ LÝ UPLOAD TÀI LIỆU (ZIP/PDF) ===
function handle_material_upload(array $file_data, ?string $old_path = null): ?string
{
    if (!isset($file_data) || $file_data['error'] !== UPLOAD_ERR_OK) {
        return $old_path; // Giữ nguyên nếu không upload mới
    }

    $max_size = 50 * 1024 * 1024; // 50MB
    if ($file_data['size'] > $max_size) throw new Exception("File tài liệu quá lớn (>50MB).");
    
    $allowed_exts = ['zip', 'rar', 'pdf', '7z'];
    $extension = strtolower(pathinfo($file_data['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowed_exts)) {
        throw new Exception("Chỉ chấp nhận file tài liệu (.zip, .rar, .pdf, .7z).");
    }

    $upload_dir = __DIR__ . '/../uploads/materials/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $unique_filename = 'material_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $target_path = $upload_dir . $unique_filename;

    if (move_uploaded_file($file_data['tmp_name'], $target_path)) {
        if ($old_path && file_exists(__DIR__ . '/../' . $old_path)) {
            unlink(__DIR__ . '/../' . $old_path);
        }
        return 'uploads/materials/' . $unique_filename;
    }
    throw new Exception("Không thể lưu file tài liệu.");
}

// === HÀM TẠO MÃ 4 KÝ TỰ NGẪU NHIÊN ===
function generate_random_code($pdo) {
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $code = '';
        for ($i = 0; $i < 4; $i++) $code .= $chars[rand(0, strlen($chars) - 1)];
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE course_code = ?");
        $stmt->execute([$code]);
    } while ($stmt->fetch());
    return $code;
}

// --- Validation ---
function validate_course($data) {
    $errors = [];
    if (empty(trim($data['name']))) $errors[] = "Tên khóa học là bắt buộc.";
    if (empty($data['category_id']) || !is_numeric($data['category_id'])) $errors[] = "Danh mục là bắt buộc.";
    if (!is_numeric($data['fee']) || $data['fee'] < 0) $errors[] = "Học phí phải là số hợp lệ.";
    if (empty(trim($data['duration']))) $errors[] = "Thời lượng là bắt buộc.";
    return $errors;
}

try {
    switch ($action) {
        
        // --- TẠO MỚI ---
        case 'create':
            $data = $_POST;
            $errors = validate_course($data);
            $image_path = null;
            $material_path = null;

            try {
                $image_path = handle_course_upload($_FILES['course_image'], null);
                if (empty($image_path)) $errors[] = "Ảnh minh họa là bắt buộc khi tạo mới.";
                
                $material_path = handle_material_upload($_FILES['course_material'], null);
                
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old_input'] = $data;
                header("Location: courses.php?action=add");
                exit;
            }

            $new_code = generate_random_code($pdo);

            $sql = "INSERT INTO courses (course_code, category_id, name, short_description, full_description, fee, duration, image_url, material_url, what_you_learn, requirements) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $new_code,
                $data['category_id'],
                $data['name'], $data['short_description'], $data['full_description'],
                $data['fee'], $data['duration'], $image_path, $material_path,
                $data['what_you_learn'], $data['requirements']
            ]);

            add_flash_message("Đã thêm khóa học (Mã: $new_code) thành công!", 'success');
            header("Location: courses.php");
            exit;

        // --- CẬP NHẬT ---
        case 'update':
            $id = (int)$_POST['id'];
            $data = $_POST;
            $errors = validate_course($data);

            // Lấy dữ liệu cũ để giữ lại file nếu không up mới
            $stmt_old = $pdo->prepare("SELECT image_url, material_url FROM courses WHERE id = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            
            $image_path = $old_data['image_url'];
            $material_path = $old_data['material_url'];

            try {
                // Xử lý ảnh
                $image_path = handle_course_upload($_FILES['course_image'], $old_data['image_url']);
                // Xử lý tài liệu (QUAN TRỌNG: Phải gọi hàm này ở đây)
                $material_path = handle_material_upload($_FILES['course_material'], $old_data['material_url']);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
            
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                header("Location: courses.php?action=edit&id=" . $id);
                exit;
            }

            $sql = "UPDATE courses SET category_id=?, name=?, short_description=?, full_description=?, fee=?, duration=?, image_url=?, material_url=?, what_you_learn=?, requirements=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['category_id'], $data['name'], $data['short_description'], $data['full_description'],
                $data['fee'], $data['duration'], $image_path, $material_path, 
                $data['what_you_learn'], $data['requirements'], $id
            ]);

            add_flash_message("Cập nhật thành công!", 'success');
            header("Location: courses.php");
            exit;
        // --- XÓA KHÓA HỌC ---
        case 'delete':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            $stmt_old = $pdo->prepare("SELECT image_url, material_url FROM courses WHERE id = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            
            if ($old_data['image_url'] && file_exists(__DIR__ . '/../' . $old_data['image_url'])) {
                unlink(__DIR__ . '/../' . $old_data['image_url']);
            }
            if ($old_data['material_url'] && file_exists(__DIR__ . '/../' . $old_data['material_url'])) {
                unlink(__DIR__ . '/../' . $old_data['material_url']);
            }

            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$id]);
            add_flash_message("Đã xóa khóa học thành công!", 'success');
            header("Location: courses.php");
            exit;

        // --- (QUAN TRỌNG) XÓA FILE TÀI LIỆU ---
        case 'delete_material':
            $id = (int)$_POST['id'];
            
            // 1. Lấy đường dẫn file hiện tại CỦA ĐÚNG ID ĐÓ
            $stmt_get = $pdo->prepare("SELECT material_url FROM courses WHERE id = ?");
            $stmt_get->execute([$id]);
            $material_url = $stmt_get->fetchColumn();

            if ($material_url) {

                // 3. Cập nhật CSDL về NULL CHỈ CHO KHÓA HỌC ĐÓ
                $stmt_update = $pdo->prepare("UPDATE courses SET material_url = NULL WHERE id = ?");
                $stmt_update->execute([$id]);

                add_flash_message("Đã xóa tài liệu đính kèm.", 'success');
            } else {
                add_flash_message("Khóa học này không có tài liệu để xóa.", 'error');
            }
            header("Location: courses.php");
            exit;
        
        default:
            add_flash_message("Hành động không hợp lệ.", 'error');
            header("Location: courses.php");
            exit;
    }
} catch (Exception $e) { 
    add_flash_message("Lỗi: " . $e->getMessage(), 'error');
    header("Location: " . (isset($_POST['id']) ? "courses.php?action=edit&id=" . $_POST['id'] : "courses.php?action=add"));
    exit;
}
?>