<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'inc/db.php';
require 'inc/functions.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    add_flash_message("Bạn phải đăng nhập để đăng ký khóa học.", 'error');
    header("Location: user_login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if ($course_id <= 0) {
    add_flash_message("Khóa học không hợp lệ.", 'error');
    header("Location: index.php");
    exit;
}

try {
    // 3. Lấy thông tin người dùng
    $stmt_user = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch();

    if (!$user) {
        add_flash_message("Lỗi: Không tìm thấy thông tin người dùng.", 'error');
        header("Location: index.php");
        exit;
    }
    
    $student_name = $user['full_name'];
    $student_email = $user['email'];
    $student_phone = $user['phone'] ?? null; 

    // 4. Kiểm tra xem đã đăng ký chưa
    $stmt_check = $pdo->prepare("SELECT id FROM registrations WHERE student_email = ? AND course_id = ?");
    $stmt_check->execute([$student_email, $course_id]);
    
    if ($stmt_check->fetch()) {
        add_flash_message("Bạn đã đăng ký khóa học này rồi.", 'error');
        header("Location: profile.php?tab=courses#courses");
        exit;
    }

    // 5. (MỚI) Tạo Mã Hóa Đơn 8 Ký Tự
    // (Tạo 4 byte ngẫu nhiên -> chuyển thành 8 ký tự Hex (0-9, A-F))
    $invoice_code = strtoupper(bin2hex(random_bytes(4)));

    // 6. Tiến hành đăng ký (Thêm invoice_code vào SQL)
    $sql = "INSERT INTO registrations (course_id, invoice_code, student_name, student_email, student_phone, status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $course_id,
        $invoice_code, // Thêm mã mới
        $student_name,
        $student_email,
        $student_phone,
        'Đang xử lý'
    ]);

    // 7. Thông báo thành công
    add_flash_message("Đăng ký khóa học thành công! (Mã đơn: $invoice_code)", 'success');
    header("Location: profile.php?tab=courses#courses");
    exit;

} catch (PDOException $e) {
    // Xử lý nếu mã bị trùng (dù rất hiếm)
    if ($e->errorInfo[1] == 1062) { // 1062 là lỗi "Duplicate entry"
         add_flash_message("Lỗi tạo mã đơn, vui lòng thử lại.", 'error');
         header("Location: course.php?id=" . $course_id);
    } else {
         add_flash_message("Lỗi CSDL: " . $e->getMessage(), 'error');
         header("Location: index.php");
    }
    exit;
}
?>