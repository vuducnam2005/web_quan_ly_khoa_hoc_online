<?php
require_once __DIR__ . '/../inc/auth.php'; // Chỉ Admin mới được vào
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: users.php");
    exit;
}

$action = $_POST['action'] ?? '';

// --- Validation chung (Cho Create/Update) ---
function validate_user($data, $is_update = false) {
    $errors = [];
    if (empty(trim($data['full_name']))) {
        $errors[] = "Họ và tên là bắt buộc.";
    }
    if (empty(trim($data['email'])) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ.";
    }
    
    // Mật khẩu chỉ bắt buộc khi tạo mới
    if (!$is_update && empty(trim($data['password']))) {
        $errors[] = "Mật khẩu là bắt buộc khi tạo mới.";
    }
    if (!empty(trim($data['password'])) && strlen($data['password']) < 6) {
         $errors[] = "Mật khẩu (nếu nhập) phải có ít nhất 6 ký tự.";
    }
    return $errors;
}

try {
    switch ($action) {
        
        // --- TẠO MỚI (Admin tạo) ---
        case 'create':
            $data = $_POST;
            $errors = validate_user($data, false);
            
            // Kiểm tra email trùng
            $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check->execute([$data['email']]);
            if ($stmt_check->fetch()) {
                $errors[] = "Email này đã tồn tại.";
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old_input'] = $data;
                header("Location: users.php?action=add");
                exit;
            }

            // Lưu mật khẩu dạng chữ thường (theo yêu cầu của bạn)
            $password_plain = $data['password']; 
            
            $sql = "INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['full_name'], $data['email'], $password_plain]);

            add_flash_message("Tạo người dùng mới thành công!", 'success');
            header("Location: users.php");
            exit;

        // --- CẬP NHẬT ---
        case 'update':
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id <= 0) {
        add_flash_message("ID người dùng không hợp lệ.", 'error');
        header("Location: users.php");
        exit;
    }

    $data = $_POST;
    $errors = validate_user($data, true);

    // Kiểm tra email trùng (ngoại trừ user hiện tại)
    $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt_check->execute([$data['email'], $id]);
    if ($stmt_check->fetch()) {
        $errors[] = "Email này đã được sử dụng bởi người dùng khác.";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $data;
        header("Location: users.php?action=edit&id=" . $id);
        exit;
    }

    $status = $data['status'] ?? 'active';

    // Lấy student_code để update bảng registrations
    $stmt_code = $pdo->prepare("SELECT student_code FROM users WHERE id = ?");
    $stmt_code->execute([$id]);
    $student_code = $stmt_code->fetchColumn();

    // Kiểm tra có đổi password hay không
    if (!empty(trim($data['password']))) {

        $password_plain = $data['password']; // đáng lẽ phải hash nhưng mình giữ nguyên theo code của bạn

        // UPDATE users
        $sql = "UPDATE users 
                SET full_name = ?, email = ?, password_hash = ?, status = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['full_name'],
            $data['email'],
            $password_plain,
            $status,
            $id
        ]);

    } else {

        // UPDATE users (không đổi password)
        $sql = "UPDATE users 
                SET full_name = ?, email = ?, status = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['full_name'],
            $data['email'],
            $status,
            $id
        ]);
    }

    // UPDATE bảng registrations dùng student_code
    $sql_reg = "UPDATE registrations 
                SET student_name = ?, student_email = ?
                WHERE student_code = ?";
    $stmt_reg = $pdo->prepare($sql_reg);
    $stmt_reg->execute([
        $data['full_name'],
        $data['email'],
        $student_code
    ]);

    add_flash_message("Cập nhật người dùng thành công!", 'success');
    header("Location: users.php");
    exit;

        // --- XÓA ---
        case 'delete':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            // TODO: Bạn có thể thêm logic kiểm tra để admin không tự xóa chính họ (nếu admin cũng là user)
            // Tuy nhiên, theo cấu trúc hiện tại (bảng admins và users riêng) thì không cần
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            add_flash_message("Đã xóa người dùng thành công!", 'success');
            header("Location: users.php");
            exit;
        
        // --- KHÓA / MỞ KHÓA ---
        case 'toggle_lock':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $current_status = $_POST['status'] ?? 'active';
            
            $new_status = ($current_status === 'active') ? 'locked' : 'active';
            
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            
            add_flash_message("Đã " . ($new_status === 'locked' ? 'khóa' : 'mở khóa') . " tài khoản!", 'success');
            header("Location: users.php");
            exit;
            
        default:
            add_flash_message("Hành động không hợp lệ.", 'error');
            header("Location: users.php");
            exit;
    }
} catch (PDOException $e) {
    add_flash_message("Lỗi CSDL: " . $e->getMessage(), 'error');
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>