<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Nạp tài nguyên
require_once 'inc/functions.php';
require_once 'inc/db.php';

// 2. Bảo vệ trang: Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    add_flash_message("Bạn phải đăng nhập để xem trang cá nhân.", 'error');
    header("Location: user_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt_check_acc = $pdo->prepare("SELECT status FROM users WHERE id = ?");
$stmt_check_acc->execute([$user_id]);
$current_status = $stmt_check_acc->fetchColumn();

// Nếu bị khóa -> Hiển thị thông báo và DỪNG LUÔN
if ($current_status === 'locked') {
    // Xóa phiên đăng nhập ngay lập tức để bảo mật
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    
    // Nạp header để có giao diện web
    require_once 'inc/header.php';
    ?>
    
    <div class="container" style="margin-top: 50px; margin-bottom: 100px; text-align: center;">
        <div style="
            background-color: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeeba; 
            padding: 40px; 
            border-radius: 8px; 
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 600px;
        ">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16" style="margin-bottom: 20px;">
                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/>
            </svg>
            <h2 style="margin-top: 0; color: #856404;">Tài khoản đã bị khóa</h2>
            <p style="font-size: 1.2rem; margin: 15px 0;">
                Vui lòng liên hệ <strong>19002005</strong> để được hỗ trợ.
            </p>
            <a href="index.php" class="btn btn-primary" style="margin-top: 10px;">Về trang chủ</a>
        </div>
    </div>

    <?php
    require_once 'inc/footer.php';
    exit; // DỪNG TOÀN BỘ CODE PHÍA SAU
}
$alert_message = ""; 
$active_tab_on_load = 'dashboard'; 

// ==========================================
// PHẦN 1: XỬ LÝ LOGIC (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            
            // --- UPLOAD AVATAR ---
            case 'upload_avatar':
                $active_tab_on_load = 'dashboard';
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['avatar'];
                    $upload_dir = 'uploads/';
                    
                    // Tạo thư mục nếu chưa có
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $file_type = mime_content_type($file['tmp_name']);
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $unique_filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
                    $target_path = $upload_dir . $unique_filename;
                    
                    if (in_array($file_type, $allowed_types) && move_uploaded_file($file['tmp_name'], $target_path)) {
                        // Xóa ảnh cũ
                        $stmt_old = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
                        $stmt_old->execute([$user_id]);
                        $old_avatar = $stmt_old->fetchColumn();
                        if ($old_avatar && file_exists($old_avatar) && !filter_var($old_avatar, FILTER_VALIDATE_URL)) {
                            unlink($old_avatar);
                        }
                        
                        // Cập nhật CSDL
                        $stmt_update = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                        $stmt_update->execute([$target_path, $user_id]);
                        $alert_message = "Cập nhật ảnh đại diện thành công!";
                    } else { throw new Exception("Lỗi file hoặc định dạng không cho phép."); }
                }
                break;
                
            // --- CẬP NHẬT THÔNG TIN & ĐỒNG BỘ ---
            case 'update_info':
                $active_tab_on_load = 'info'; 
                $full_name = trim($_POST['full_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                
                // Kiểm tra trùng email (trừ chính mình)
                $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt_check->execute([$email, $user_id]);
                if ($stmt_check->fetch()) {
                    $_SESSION['errors'] = ['info' => "Email này đã được sử dụng bởi tài khoản khác."];
                    $_SESSION['old_input'] = $_POST;
                } else {
                    // 1. Lấy thông tin CŨ và Mã HV
                    $stmt_old = $pdo->prepare("SELECT email, student_code FROM users WHERE id = ?");
                    $stmt_old->execute([$user_id]);
                    $old_data = $stmt_old->fetch();
                    $old_email = $old_data['email'];
                    $student_code = $old_data['student_code'];

                    // 2. Cập nhật bảng USERS
                    $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$full_name, $email, $phone, $user_id]);
                    
                    // 3. (FIX LỖI QUAN TRỌNG) ĐỒNG BỘ BẢNG ĐƠN HÀNG
                    // Cập nhật thông tin mới vào các đơn hàng cũ dựa trên Mã Học Viên (hoặc email cũ)
                    $sql_sync = "UPDATE registrations 
                                 SET student_name = ?, student_email = ?, student_phone = ?
                                 WHERE student_code = ? OR student_email = ?";
                    $stmt_sync = $pdo->prepare($sql_sync);
                    $stmt_sync->execute([$full_name, $email, $phone, $student_code, $old_email]);

                    $_SESSION['user_name'] = $full_name;
                    $alert_message = "Cập nhật thông tin thành công!";
                }
                break;

            // --- ĐỔI MẬT KHẨU ---
            case 'change_password':
                $active_tab_on_load = 'password';
                $old_password = $_POST['old_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                $stmt_pass = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                $stmt_pass->execute([$user_id]);
                $current_pass = $stmt_pass->fetchColumn();
                
                $errors = [];
                // Lưu ý: Đang dùng so sánh chuỗi thường (theo yêu cầu của bạn)
                if ($old_password !== $current_pass) $errors[] = "Mật khẩu cũ không đúng.";
                if ($new_password === $current_pass) $errors[] = "Mật khẩu mới không được trùng mật khẩu cũ.";
                if (strlen($new_password) < 6) $errors[] = "Mật khẩu quá ngắn (min 6 ký tự).";
                if ($new_password !== $confirm_password) $errors[] = "Xác nhận mật khẩu sai.";

                if (!empty($errors)) {
                    $_SESSION['errors'] = ['password' => implode('\n', $errors)];
                } else {
                    $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$new_password, $user_id]);
                    $alert_message = "Đổi mật khẩu thành công!";
                }
                break;
        }
    } catch (Exception $e) { 
        $alert_message = "Lỗi: " . $e->getMessage(); 
    }
}
// === KẾT THÚC LOGIC XỬ LÝ FORM ===


// ==========================================
// PHẦN 2: LẤY DỮ LIỆU ĐỂ HIỂN THỊ
// ==========================================

// Lấy thông báo lỗi từ session (nếu có redirect)
if (isset($_SESSION['flash_message_error'])) {
    $alert_message = "Lỗi: " . $_SESSION['flash_message_error'];
    unset($_SESSION['flash_message_error']);
}

// 1. Lấy thông tin user
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

// === (MỚI) KIỂM TRA TÀI KHOẢN CÒN TỒN TẠI KHÔNG ===
if (!$user) {
    // Nếu không tìm thấy user trong CSDL (đã bị Admin xóa)
    // 1. Xóa session
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    
    // 2. Báo lỗi và chuyển hướng
    add_flash_message("Tài khoản của bạn không tồn tại hoặc đã bị xóa.", 'error');
    header("Location: user_login.php");
    exit;
}
$my_student_code = $user['student_code']; // Mã học viên quan trọng

// 2. Lấy thống kê (Dựa trên Student Code là chính xác nhất)
$stmt_stat = $pdo->prepare("
    SELECT SUM(c.fee) as total_spent 
    FROM registrations r 
    JOIN courses c ON r.course_id = c.id 
    WHERE r.student_code = ? AND r.status = 'Đã xác nhận'
");
$stmt_stat->execute([$my_student_code]);
$total_spent = (float)($stmt_stat->fetchColumn() ?? 0);

// 3. Lấy danh sách khóa học (Dựa trên Student Code)
$stmt_courses = $pdo->prepare("
    SELECT r.id as registration_id, r.registered_at, r.status, 
           c.name as course_name, c.fee, c.material_url 
    FROM registrations r 
    JOIN courses c ON r.course_id = c.id 
    WHERE r.student_code = ? 
    ORDER BY r.registered_at DESC
");
$stmt_courses->execute([$my_student_code]);
$purchased_courses = $stmt_courses->fetchAll();

// Avatar
$avatar_url = 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; // Ảnh mặc định an toàn

if (!empty($user['avatar'])) {
    // 1. Nếu là link online (bắt đầu bằng http) -> Dùng luôn
    // (Logic này chấp nhận cả link có tiếng Việt như ui-avatars)
    if (strpos($user['avatar'], 'http') === 0) {
        $avatar_url = $user['avatar'];
    } 
    // 2. Nếu là file trong máy -> Kiểm tra tồn tại
    elseif (file_exists($user['avatar'])) {
        $avatar_url = $user['avatar'];
    }
}

$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']); 

// Logic Tab
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $active_tab_on_load = 'dashboard';
    $current_tab = explode('#', $_GET['tab'] ?? '')[0];
    if (in_array($current_tab, ['dashboard', 'courses', 'info', 'password'])) $active_tab_on_load = $current_tab;
}
if (isset($errors['info'])) $active_tab_on_load = 'info';
if (isset($errors['password'])) $active_tab_on_load = 'password';

require_once 'inc/header.php'; 
?>

<div class="profile-layout">
    
    <aside class="profile-sidebar">
        <div class="profile-avatar">
            <img src="<?php echo $avatar_url; ?>" alt="Ảnh đại diện">
            
            <h3><?php echo h($user['full_name']); ?></h3>
            <?php if(!empty($my_student_code)): ?>
                <p style="font-weight:bold; color:var(--primary-color); margin-bottom:5px;"><?php echo h($my_student_code); ?></p>
            <?php endif; ?>
            <p><?php echo h($user['email']); ?></p>
            
            <form action="profile.php?tab=dashboard#dashboard" method="POST" enctype="multipart/form-data" class="avatar-upload-form">
                <input type="hidden" name="action" value="upload_avatar">
                <label for="avatar_file" class="btn btn-secondary btn-small">Đổi ảnh</label>
                <input type="file" id="avatar_file" name="avatar" class="hidden-file-input" onchange="this.form.submit();">
            </form>
        </div>
        <nav class="profile-nav">
            <ul>
                <li><a href="profile.php?tab=dashboard#dashboard" class="<?php echo $active_tab_on_load === 'dashboard' ? 'active' : ''; ?>">Tổng quan</a></li>
                <li><a href="profile.php?tab=courses#courses" class="<?php echo $active_tab_on_load === 'courses' ? 'active' : ''; ?>">Khóa học của tôi</a></li>
                <li><a href="profile.php?tab=info#info" class="<?php echo $active_tab_on_load === 'info' ? 'active' : ''; ?>">Thông tin cá nhân</a></li>
                <li><a href="profile.php?tab=password#password" class="<?php echo $active_tab_on_load === 'password' ? 'active' : ''; ?>">Đổi mật khẩu</a></li>
            </ul>
        </nav>
    </aside>

    <div class="profile-content">
         
        <div id="dashboard" class="profile-tab-content <?php echo $active_tab_on_load === 'dashboard' ? 'active' : ''; ?>">
            <h2>Tổng quan</h2>
            <?php if (isset($errors['avatar'])): ?> <div class="flash-message error"><?php echo h($errors['avatar']); ?></div> <?php endif; ?>
            
            <div class="stat-card profile-stat">
                <h3>Tổng tiền đã chi</h3>
                <p><?php echo number_format($total_spent, 0, ',', '.'); ?> VNĐ</p>
            </div>

            <h3>Khóa học mới nhất</h3>
             <?php if (empty($purchased_courses)): ?>
                <p>Bạn chưa đăng ký khóa học nào.</p>
            <?php else: ?>
                <div class="admin-table-responsive">
                    <table class="admin-table">
                         <thead> <tr> <th>Tên khóa học</th> <th>Ngày đăng ký</th> <th>Trạng thái</th> </tr> </thead>
                        <tbody>
                            <?php foreach (array_slice($purchased_courses, 0, 3) as $course): ?>
                                <tr>
                                    <td data-label="Khóa học"><?php echo h($course['course_name']); ?></td>
                                    <td data-label="Ngày ĐK"><?php echo date('d/m/Y', strtotime($course['registered_at'])); ?></td>
                                    <td data-label="Trạng thái"><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $course['status'])); ?>"><?php echo h($course['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="profile.php?tab=courses#courses" class="btn btn-secondary" style="margin-top: 1rem; border-radius: 5px;">Xem tất cả</a>
            <?php endif; ?>
        </div>

        <div id="courses" class="profile-tab-content <?php echo $active_tab_on_load === 'courses' ? 'active' : ''; ?>">
            <h2>Khóa học của tôi</h2>
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead> <tr> <th>Tên khóa học</th> <th>Học phí</th> <th>Ngày đăng ký</th> <th>Trạng thái</th> <th>Hành động</th> </tr> </thead>
                    <tbody>
                        <?php if (empty($purchased_courses)): ?> <tr><td colspan="5">Bạn chưa đăng ký khóa học nào.</td></tr> <?php else: ?>
                            <?php foreach ($purchased_courses as $course): ?>
                                <tr>
                                    <td data-label="Khóa học"><?php echo h($course['course_name']); ?></td>
                                    <td data-label="Học phí"><?php echo number_format($course['fee'], 0, ',', '.'); ?> VNĐ</td>
                                    <td data-label="Ngày ĐK"><?php echo date('d/m/Y', strtotime($course['registered_at'])); ?></td>
                                    <td data-label="Trạng thái"><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $course['status'])); ?>">
                                        <?php echo h($course['status']); ?></span></td>
                                    <td data-label="Hành động"> 
                                        <div style="display:flex; gap:5px;">
                                            <a href="invoice.php?id=<?php echo h($course['registration_id']); ?>" class="btn btn-small btn-secondary">Hóa đơn</a>
                                            <?php if ($course['status'] === 'Đã xác nhận' && !empty($course['material_url'])): ?>
                                                <a href="<?php echo h($course['material_url']); ?>" class="btn btn-small btn-primary" download>Tải về</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="info" class="profile-tab-content <?php echo $active_tab_on_load === 'info' ? 'active' : ''; ?>">
            <h2>Thông tin cá nhân</h2>
            <?php if (isset($errors['info'])): ?> <div class="flash-message error"><?php echo h($errors['info']); ?></div> <?php endif; ?>
            <form action="profile.php?tab=info#info" method="POST" class="admin-form">
                <input type="hidden" name="action" value="update_info">
                <div class="form-group"> <label>Họ và tên</label> <input type="text" name="full_name" value="<?php echo h($old_input['full_name'] ?? $user['full_name']); ?>"> </div>
                <div class="form-group"> <label>Email</label> <input type="email" name="email" value="<?php echo h($old_input['email'] ?? $user['email']); ?>"> </div>
                <div class="form-group"> <label>Số điện thoại</label> <input type="tel" name="phone" value="<?php echo h($old_input['phone'] ?? $user['phone'] ?? ''); ?>"> </div>
                <div class="form-group"> <button type="submit" class="btn btn-primary" style="border-radius: 5px;">Cập nhật</button> </div>
            </form>
        </div>

        <div id="password" class="profile-tab-content <?php echo $active_tab_on_load === 'password' ? 'active' : ''; ?>">
            <h2>Đổi mật khẩu</h2>
            <?php if (isset($errors['password'])): ?> <div class="flash-message error"><?php echo h($errors['password']); ?></div> <?php endif; ?>
            <form action="profile.php?tab=password#password" method="POST" class="admin-form">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group"> <label>Mật khẩu cũ</label> <input type="password" name="old_password"> </div>
                <div class="form-group"> <label>Mật khẩu mới</label> <input type="password" name="new_password"> </div>
                <div class="form-group"> <label>Xác nhận mới</label> <input type="password" name="confirm_password"> </div>
                <div class="form-group"> <button type="submit" class="btn btn-primary" style="border-radius: 5px;">Đổi mật khẩu</button> </div>
            </form>
        </div>
    </div>
</div>
<div style="height: 250px;">

</div>
<?php require 'inc/footer.php'; ?>

<script>
    window.onload = function() {
        <?php 
        if (!empty($alert_message)) { 
            echo "alert('" . addslashes($alert_message) . "');"; 
        } elseif (!empty($errors)) {
             // Nếu có lỗi validation (ví dụ mật khẩu sai), cũng hiện alert
             $all_errors = "";
             foreach($errors as $err) $all_errors .= $err . "\\n";
             echo "alert('" . addslashes($all_errors) . "');";
        }
        ?>
    };
</script>