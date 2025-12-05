<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bảo vệ: Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

require 'inc/db.php';
require 'inc/functions.php'; // Nạp file functions (để gọi add_flash_message)

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
try {
    switch ($action) {
        case 'upload_avatar':
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar'];
                $upload_dir = 'uploads/';
                $max_size = 2 * 1024 * 1024;
                if ($file['size'] > $max_size)
                    throw new Exception("File quá lớn (dưới 2MB).");
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = mime_content_type($file['tmp_name']);
                if (!in_array($file_type, $allowed_types))
                    throw new Exception("Chỉ chấp nhận JPG, PNG, GIF.");
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $unique_filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
                $target_path = $upload_dir . $unique_filename;
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $stmt_old = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
                    $stmt_old->execute([$user_id]);
                    $old_avatar = $stmt_old->fetchColumn();
                    if ($old_avatar && file_exists($old_avatar) && !filter_var($old_avatar, FILTER_VALIDATE_URL)) {
                        unlink($old_avatar);
                    }
                    $stmt_update = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $stmt_update->execute([$target_path, $user_id]);
                    add_flash_message("Cập nhật ảnh đại diện thành công!", 'success');
                } else {
                    throw new Exception("Không thể di chuyển file.");
                }
            } else {
                throw new Exception("Lỗi tải file. (Mã lỗi: " . ($_FILES['avatar']['error'] ?? 'unknown') . ")");
            }
            header("Location: profile.php");
            exit;


        case 'update_info':
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $errors = [];

            if (empty($full_name))
                $errors[] = "Họ và tên là bắt buộc.";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                $errors[] = "Email không hợp lệ.";

            // Kiểm tra email trùng với user khác
            $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt_check->execute([$email, $user_id]);
            if ($stmt_check->fetch())
                $errors[] = "Email này đã được sử dụng.";

            if (!empty($errors)) {
                $_SESSION['errors'] = ['info' => implode(' ', $errors)];
                $_SESSION['old_input'] = $_POST;
            } else {

                // Lấy student_code từ bảng users
                $stmt_code = $pdo->prepare("SELECT student_code FROM users WHERE id = ?");
                $stmt_code->execute([$user_id]);
                $student_code = $stmt_code->fetchColumn();

                // UPDATE bảng users
                $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$full_name, $email, $phone, $user_id]);

                // UPDATE bảng registrations theo student_code
                $sql_reg = "UPDATE registrations 
                    SET student_name = ?, student_email = ?, student_phone = ?
                    WHERE student_code = ?";
                $stmt_reg = $pdo->prepare($sql_reg);
                $stmt_reg->execute([$full_name, $email, $phone, $student_code]);

                // Cập nhật session
                $_SESSION['user_name'] = $full_name;
                add_flash_message("Cập nhật thông tin thành công!", 'success');
            }

            header("Location: profile.php?tab=info#info");
            exit;

        // --- XỬ LÝ ĐỔI MẬT KHẨU ---
        case 'change_password':
            $old_password = $_POST['old_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $errors = [];

            $stmt_pass = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt_pass->execute([$user_id]);
            $current_pass_plain = $stmt_pass->fetchColumn();

            if ($old_password !== $current_pass_plain)
                $errors[] = "Mật khẩu hiện tại không đúng.";
            if (!empty($new_password) && $new_password === $current_pass_plain)
                $errors[] = "Mật khẩu mới không được trùng với mật khẩu cũ.";
            if (strlen($new_password) < 6)
                $errors[] = "Mật khẩu mới phải có ít nhất 6 ký tự.";
            if ($new_password !== $confirm_password)
                $errors[] = "Mật khẩu xác nhận không khớp.";

            if (!empty($errors)) {
                // Lưu lỗi validation vào session (để tab tự hiển thị)
                $_SESSION['errors'] = ['password' => implode(' ', $errors)];
            } else {
                // Lưu mật khẩu mới và BÁO THÀNH CÔNG
                $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$new_password, $user_id]);

                add_flash_message("Đổi mật khẩu thành công!", 'success');
            }
            header("Location: profile.php?tab=password#password");
            exit;
    }
} catch (Exception $e) {
    add_flash_message($e->getMessage(), 'error');
    header("Location: profile.php");
    exit;
}
?>