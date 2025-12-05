<?php
// Bắt đầu bộ đệm đầu ra để tránh lỗi header/whitespace
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'inc/db.php';
require 'inc/functions.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Kiểm tra AJAX
$is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
$action = $_POST['action'] ?? ($_GET['action'] ?? '');

try {
    switch ($action) {
        case 'add':
            // 1. CHẶN ADMIN
            if (isset($_SESSION['admin_id'])) {
                $msg = "Bạn đang là Quản trị viên. Không thể mua hàng.";
                if ($is_ajax)
                    send_json_response('error', $msg);
                else {
                    add_flash_message($msg, 'error');
                    header("Location: index.php");
                }
                exit;
            }

            // 2. KIỂM TRA ĐĂNG NHẬP
            if (!isset($_SESSION['user_id'])) {
                if ($is_ajax)
                    send_json_response('error', 'login_required');
                else {
                    add_flash_message("Vui lòng đăng nhập.", 'error');
                    header("Location: user_login.php");
                }
                exit;
            }

            // 3. KIỂM TRA KHÓA
            $stmt = $pdo->prepare("SELECT status, student_code FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user_data = $stmt->fetch();

            if ($user_data['status'] === 'locked') {
                unset($_SESSION['user_id']);
                $msg = "Tài khoản bị khóa. Liên hệ 19002005.";
                if ($is_ajax)
                    send_json_response('error', $msg);
                else {
                    add_flash_message($msg, 'error');
                    header("Location: user_login.php");
                }
                exit;
            }

            $course_id = (int) ($_POST['course_id'] ?? 0);

            if ($course_id > 0) {
                // 4. KIỂM TRA ĐÃ MUA
                $stmt_check = $pdo->prepare("SELECT id FROM registrations WHERE student_code = ? AND course_id = ?");
                $stmt_check->execute([$user_data['student_code'], $course_id]);

                if ($stmt_check->fetch()) {
                    if ($is_ajax)
                        send_json_response('error', 'Bạn đã sở hữu khóa học này rồi!');
                    else
                        add_flash_message("Bạn đã mua khóa học này rồi.", 'error');
                    exit;
                }

                // Thêm vào giỏ
                if (!in_array($course_id, $_SESSION['cart'])) {
                    $_SESSION['cart'][] = $course_id;
                    if (!$is_ajax)
                        add_flash_message("Đã thêm vào giỏ hàng!", 'success');
                } else {
                    if ($is_ajax)
                        send_json_response('error', 'Khóa học đã có trong giỏ hàng.');
                    else
                        add_flash_message("Khóa học đã có trong giỏ hàng.", 'error');
                    exit;
                }
            }

            // Thành công
            if ($is_ajax) {
                send_json_response('success', '', count($_SESSION['cart']));
            }
            header("Location: cart.php");
            exit;

        // ... (Các case remove, checkout giữ nguyên logic cũ) ...
        case 'remove':
            $course_id = (int) ($_GET['id'] ?? 0);
            if (($key = array_search($course_id, $_SESSION['cart'])) !== false) {
                unset($_SESSION['cart'][$key]);
                add_flash_message("Đã xóa khóa học.", 'success');
            }
            header("Location: cart.php");
            exit;

        case 'checkout':
            // (Code checkout giữ nguyên như file trước)
            // ... Để ngắn gọn tôi không paste lại đoạn checkout dài, bạn giữ nguyên logic cũ
            if (!isset($_SESSION['user_id'])) {
                header("Location: user_login.php");
                exit;
            }
            if (isset($_SESSION['admin_id'])) {
                header("Location: index.php");
                exit;
            }
            $selected_ids = $_POST['selected_courses'] ?? [];
            if (empty($selected_ids)) {
                add_flash_message("Vui lòng chọn ít nhất 1 khóa học để thanh toán.", 'error');
                header("Location: cart.php");
                exit;
            }
            $stmt_u = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt_u->execute([$_SESSION['user_id']]);
            $user = $stmt_u->fetch();

            if ($user['status'] === 'locked') {
                unset($_SESSION['user_id']);
                header("Location: user_login.php");
                exit;
            }
            if (empty($_SESSION['cart'])) {
                header("Location: index.php");
                exit;
            }
            if (empty($user['student_code'])) {
                $new_code = '177102' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $pdo->prepare("UPDATE users SET student_code = ? WHERE id = ?")->execute([$new_code, $user['id']]);
                $user['student_code'] = $new_code;
            }
            $success_count = 0;
            foreach ($selected_ids as $c_id) {
                $c_id = (int) $c_id;
                if (in_array($c_id, $_SESSION['cart'])) {
                    $chk = $pdo->prepare("SELECT id FROM registrations WHERE student_code = ? AND course_id = ?");
                    $chk->execute([$user['student_code'], $c_id]);
                    if (!$chk->fetch()) {
                        $invoice_code = strtoupper(bin2hex(random_bytes(4)));
                        $sql = "INSERT INTO registrations (course_id, invoice_code, student_code, student_name, student_email, student_phone, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);

                        // Chỉ giữ lại dòng này (Vừa thực thi, vừa kiểm tra kết quả)
                        if (
                            $stmt->execute([
                                $c_id,                    // ID khóa học
                                $invoice_code,            // Mã hóa đơn
                                $user['student_code'],    // Mã học viên
                                $user['full_name'],       // Tên
                                $user['email'],           // Email
                                $user['phone'],           // SĐT
                                'Đang xử lý'              // Trạng thái
                            ])
                        ) {

                            $success_count++;

                            // Xóa khỏi giỏ hàng sau khi mua thành công
                            $key = array_search($c_id, $_SESSION['cart']);
                            if ($key !== false) {
                                unset($_SESSION['cart'][$key]);
                            }
                        }

                    } else {
                        // Nếu đã mua rồi nhưng vẫn còn trong giỏ -> Xóa khỏi giỏ
                        $key = array_search($c_id, $_SESSION['cart']);
                        if ($key !== false)
                            unset($_SESSION['cart'][$key]);
                    }
                }
            }
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            if ($success_count > 0) {
                add_flash_message("Thanh toán thành công $success_count khóa học! Vui lòng chờ duyệt.", 'success');
                header("Location: profile.php?tab=courses#courses");
            } else {
                add_flash_message("Không có đơn hàng nào được tạo.", 'info');
                header("Location: cart.php");
            }
            exit;
    }
} catch (Exception $e) {
    if ($is_ajax)
        send_json_response('error', $e->getMessage());
    else {
        add_flash_message("Lỗi: " . $e->getMessage(), 'error');
        header("Location: cart.php");
    }
    exit;
}

// Hàm trả về JSON sạch
function send_json_response($status, $message = '', $count = 0)
{
    // Xóa mọi output trước đó (khoảng trắng, warning...)
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message, 'count' => $count]);
    exit;
}
?>