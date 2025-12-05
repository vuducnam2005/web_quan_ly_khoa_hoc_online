<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'inc/db.php';
require 'inc/functions.php';

$action = $_POST['action'] ?? '';
$course_id = (int)($_POST['course_id'] ?? 0);
$redirect_url = "course.php?id=" . $course_id . "#comments-section";

$user_id = $_SESSION['user_id'] ?? null;
$admin_id = $_SESSION['admin_id'] ?? null;

// Nếu đang đăng nhập cả 2, ưu tiên User cho việc Like/Comment
// NHƯNG: Nếu là Admin thì giữ quyền Admin để Xóa/Trả lời
if ($user_id && !$admin_id) {
    // Chỉ có user
} elseif ($user_id && $admin_id) {
    // Cả 2: Nếu action là delete_comment thì dùng Admin, ngược lại dùng User
    if ($action !== 'delete_comment' && $action !== 'admin_reply') {
        $admin_id = null; 
    }
}

if (!$user_id && !$admin_id) {
    add_flash_message("Bạn cần đăng nhập để thực hiện hành động này.", 'error');
    header("Location: user_login.php"); 
    exit;
}

// Kiểm tra quyền cơ bản
$can_interact = false;
if ($admin_id) {
    $can_interact = true; // Admin làm gì cũng được
} elseif ($user_id && has_bought_course($user_id, $course_id)) {
    $can_interact = true; // User phải mua mới được
}

if (!$can_interact) {
    add_flash_message("Bạn cần mua khóa học này để đánh giá hoặc bình luận.", 'error');
    header("Location: " . $redirect_url);
    exit;
}

try {
    switch ($action) {
        // --- XỬ LÝ LIKE / DISLIKE ---
        case 'rate':
            $type = $_POST['type'] ?? 'like'; 
            $sql_check = "SELECT id FROM course_likes WHERE course_id = ? AND " . ($admin_id ? "admin_id = ?" : "user_id = ?");
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$course_id, ($admin_id ?: $user_id)]);
            $existing = $stmt_check->fetch();

            if ($existing) {
                $sql_update = "UPDATE course_likes SET type = ? WHERE id = ?";
                $pdo->prepare($sql_update)->execute([$type, $existing['id']]);
            } else {
                $sql_insert = "INSERT INTO course_likes (course_id, user_id, admin_id, type) VALUES (?, ?, ?, ?)";
                $pdo->prepare($sql_insert)->execute([$course_id, $user_id, $admin_id, $type]);
            }

            // Cập nhật count
            $likes = $pdo->query("SELECT COUNT(*) FROM course_likes WHERE course_id=$course_id AND type='like'")->fetchColumn();
            $dislikes = $pdo->query("SELECT COUNT(*) FROM course_likes WHERE course_id=$course_id AND type='dislike'")->fetchColumn();
            $pdo->prepare("UPDATE courses SET likes_count = ?, dislikes_count = ? WHERE id = ?")->execute([$likes, $dislikes, $course_id]);

            header("Location: " . $redirect_url);
            exit;

        // --- XỬ LÝ BÌNH LUẬN ---
        case 'comment':
            $content = trim($_POST['content'] ?? '');
            $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

            if (!empty($content)) {
                $sql = "INSERT INTO course_comments (course_id, user_id, admin_id, parent_id, content) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$course_id, $user_id, $admin_id, $parent_id, $content]);

                $count = $pdo->query("SELECT COUNT(*) FROM course_comments WHERE course_id=$course_id")->fetchColumn();
                $pdo->prepare("UPDATE courses SET comments_count = ? WHERE id = ?")->execute([$count, $course_id]);

                add_flash_message("Đã gửi bình luận.", 'success');
            }
            header("Location: " . $redirect_url);
            exit;

        // --- (MỚI) ADMIN XÓA BÌNH LUẬN ---
        case 'delete_comment':
            if (!$admin_id) {
                add_flash_message("Chỉ Admin mới có quyền xóa bình luận.", 'error');
                header("Location: " . $redirect_url);
                exit;
            }
            
            $comment_id = (int)($_POST['comment_id'] ?? 0);
            if ($comment_id > 0) {
                // Xóa bình luận (và các câu trả lời của nó - nếu thiết kế DB có ON DELETE CASCADE thì tự mất)
                // Ở đây ta xóa thủ công cho chắc
                $pdo->prepare("DELETE FROM course_comments WHERE id = ? OR parent_id = ?")->execute([$comment_id, $comment_id]);
                
                // Cập nhật lại số lượng
                $count = $pdo->query("SELECT COUNT(*) FROM course_comments WHERE course_id=$course_id")->fetchColumn();
                $pdo->prepare("UPDATE courses SET comments_count = ? WHERE id = ?")->execute([$count, $course_id]);

                add_flash_message("Đã xóa bình luận.", 'success');
            }
            header("Location: " . $redirect_url);
            exit;
    }
} catch (Exception $e) {
    add_flash_message("Lỗi: " . $e->getMessage(), 'error');
    header("Location: " . $redirect_url);
    exit;
}
?>