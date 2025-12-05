<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: registrations.php");
    exit;
}

// Lấy các tham số chung
$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$redirect_query = $_POST['redirect_query'] ?? ''; 
$redirect_url = "registrations.php?" . $redirect_query;

if ($id <= 0) {
    add_flash_message("ID đăng ký không hợp lệ.", 'error');
    header("Location: " . $redirect_url);
    exit;
}

try {
    switch ($action) {
        
        // --- CASE 1: CẬP NHẬT TRẠNG THÁI ---
        case 'update_status':
            $status = $_POST['status'] ?? '';
            $possible_statuses = ['Đang xử lý', 'Đã xác nhận', 'Đã hủy'];

            if (in_array($status, $possible_statuses)) {
                
                // 1. Lấy thông tin hiện tại của đơn đăng ký (để biết khóa học nào và đã tính chưa)
                $stmt_get = $pdo->prepare("SELECT course_id, is_counted FROM registrations WHERE id = ?");
                $stmt_get->execute([$id]);
                $reg_info = $stmt_get->fetch();

                if ($reg_info) {
                    $course_id = $reg_info['course_id'];
                    $is_counted = (int)$reg_info['is_counted'];

                    // 2. Cập nhật trạng thái đơn hàng
                    $stmt = $pdo->prepare("UPDATE registrations SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $id]);

                    // 3. (MỚI) LOGIC TĂNG SỐ HỌC VIÊN
                    // Nếu trạng thái mới là "Đã xác nhận" VÀ đơn này chưa được tính (is_counted = 0)
                    if ($status === 'Đã xác nhận' && $is_counted === 0) {
                        
                        // A. Tăng số học viên của khóa học lên 1
                        $stmt_inc = $pdo->prepare("UPDATE courses SET students_count = students_count + 1 WHERE id = ?");
                        $stmt_inc->execute([$course_id]);

                        // B. Đánh dấu đơn này là "đã tính"
                        $stmt_mark = $pdo->prepare("UPDATE registrations SET is_counted = 1 WHERE id = ?");
                        $stmt_mark->execute([$id]);
                    }
                    // (Tùy chọn: Nếu muốn giảm khi hủy thì thêm logic ngược lại ở đây, nhưng hiện tại ta chỉ tăng)

                    add_flash_message("Cập nhật trạng thái thành công!", 'success');
                } else {
                    add_flash_message("Không tìm thấy đơn đăng ký.", 'error');
                }

            } else {
                add_flash_message("Trạng thái không hợp lệ.", 'error');
            }
            break;

        // --- CASE 2: XÓA ĐĂNG KÝ ---
        case 'delete_registration':
            // Nếu xóa đơn "Đã xác nhận", ta có nên giảm số học viên không? 
            // Thường là có, để số liệu chính xác.
            
            // 1. Lấy thông tin trước khi xóa
            $stmt_get = $pdo->prepare("SELECT course_id, is_counted FROM registrations WHERE id = ?");
            $stmt_get->execute([$id]);
            $reg_info = $stmt_get->fetch();

            if ($reg_info && $reg_info['is_counted'] == 1) {
                // Nếu đơn này đã được tính, khi xóa phải trừ đi
                $stmt_dec = $pdo->prepare("UPDATE courses SET students_count = students_count - 1 WHERE id = ?");
                $stmt_dec->execute([$reg_info['course_id']]);
            }

            // 2. Xóa đơn
            $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
            $stmt->execute([$id]);
            add_flash_message("Đã xóa vĩnh viễn đăng ký ID #" . $id, 'success');
            break;

        default:
            add_flash_message("Hành động không hợp lệ.", 'error');
            break;
    }
} catch (PDOException $e) {
    add_flash_message("Lỗi CSDL: " . $e->getMessage(), 'error');
}

// Quay lại trang danh sách
header("Location: " . $redirect_url);
exit;
?>