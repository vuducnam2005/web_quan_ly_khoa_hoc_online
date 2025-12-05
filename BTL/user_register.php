<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'inc/db.php';
require 'inc/functions.php';

$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $form_errors = [];

    if (empty($full_name)) {
        $form_errors['full_name'] = "Họ và tên là bắt buộc.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_errors['email'] = "Email không hợp lệ.";
    }
    if (strlen($password) < 6) {
        $form_errors['password'] = "Mật khẩu phải có ít nhất 6 ký tự.";
    }
    if ($password !== $password_confirm) {
        $form_errors['password_confirm'] = "Mật khẩu nhập lại không khớp.";
    }

    // Kiểm tra email đã tồn tại chưa
    if (empty($form_errors['email'])) {
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            $form_errors['email'] = "Email này đã được sử dụng. Vui lòng chọn email khác.";
        }
    }

    if (!empty($form_errors)) {
        $_SESSION['errors'] = $form_errors;
        $_SESSION['old_input'] = $_POST;
        header("Location: user_register.php");
        exit;
    }

    // Không có lỗi -> Tạo tài khoản
    try {
       $password_plain = $password; 
        
        // 2. Tạo Mã học viên (177102 + 4 số ngẫu nhiên)
        $student_code = '177102' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // 3. (MỚI) TẠO AVATAR TỰ ĐỘNG THEO TÊN
        // urlencode để biến "Nguyễn Văn A" thành "Nguy%E1%BB%85n+V%C4%83n+A" (tránh lỗi link)
        $encoded_name = urlencode($full_name);
        $avatar_url = "https://ui-avatars.com/api/?background=random&color=fff&size=150&name={$encoded_name}";

        // 4. THÊM CỘT avatar VÀO CÂU LỆNH INSERT
        $sql = "INSERT INTO users (student_code, full_name, email, password_hash, avatar) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_code, $full_name, $email, $password_plain, $avatar_url]);
        
        // Tự động đăng nhập sau khi đăng ký
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $full_name;
        
        add_flash_message("Đăng ký thành công! Mã học viên: $student_code", 'success');
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        add_flash_message("Lỗi CSDL: " . $e->getMessage(), 'error');
        header("Location: user_register.php");
        exit;
    }
}

require 'inc/header.php';
?>
<h1>Đăng ký Tài khoản Học viên</h1>
<p>Tạo tài khoản để theo dõi và đăng ký các khóa học của bạn.</p>

<form action="user_register.php" method="POST" class="user-auth-form" novalidate>
    <div class="form-group">
        <label for="full_name">Họ và tên *</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo h($old_input['full_name'] ?? ''); ?>">
        <?php if (isset($errors['full_name'])): ?>
            <span class="form-error"><?php echo h($errors['full_name']); ?></span>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" value="<?php echo h($old_input['email'] ?? ''); ?>">
        <?php if (isset($errors['email'])): ?>
            <span class="form-error"><?php echo h($errors['email']); ?></span>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="password">Mật khẩu (ít nhất 6 ký tự) *</label>
        <input type="password" id="password" name="password">
        <?php if (isset($errors['password'])): ?>
            <span class="form-error"><?php echo h($errors['password']); ?></span>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="password_confirm">Nhập lại mật khẩu *</label>
        <input type="password" id="password_confirm" name="password_confirm">
        <?php if (isset($errors['password_confirm'])): ?>
            <span class="form-error"><?php echo h($errors['password_confirm']); ?></span>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Đăng ký</button>
    </div>
    <p>Đã có tài khoản? <a href="user_login.php">Đăng nhập ngay</a></p>
</form>

<?php require 'inc/footer.php'; ?>