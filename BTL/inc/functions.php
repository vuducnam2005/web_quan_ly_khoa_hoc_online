<?php
// inc/functions.php

/**
 * Chuyển đổi các ký tự đặc biệt thành các thực thể HTML.
 */
function h(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Thêm một thông báo (flash message) vào session.
 */
function add_flash_message(string $text, string $type = 'success')
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = ['text' => $text, 'type' => $type];
}

/**
 * Lấy các thông báo (dùng cho popup/alert)
 */
function get_flash_messages(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']); 
    return $messages;
}

/**
 * Hiển thị thông báo (dùng cho header cũ, giữ lại để tránh lỗi)
 */
function display_flash_messages()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $message) {
            $type = h($message['type']); 
            $text = h($message['text']);
            echo "<div class=\"flash-message {$type}\">{$text}</div>";
        }
        unset($_SESSION['flash_messages']);
    }
}

/**
 * (ĐÃ SỬA LỖI) Hàm định dạng thời gian (Ví dụ: 2 giờ trước)
 * Phiên bản mới tương thích PHP 8.2+
 */
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Tính toán tuần thủ công thay vì gán vào $diff->w
    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);

    // Tạo mảng cấu hình ánh xạ giá trị và tên
    $string = array(
        'y' => ['val' => $diff->y, 'text' => 'năm'],
        'm' => ['val' => $diff->m, 'text' => 'tháng'],
        'w' => ['val' => $weeks,   'text' => 'tuần'],
        'd' => ['val' => $days,    'text' => 'ngày'],
        'h' => ['val' => $diff->h, 'text' => 'giờ'],
        'i' => ['val' => $diff->i, 'text' => 'phút'],
        's' => ['val' => $diff->s, 'text' => 'giây'],
    );

    $result = [];
    foreach ($string as $k => $v) {
        if ($v['val'] > 0) {
            $result[] = $v['val'] . ' ' . $v['text'];
        }
    }

    if (!$full) $result = array_slice($result, 0, 1);
    return $result ? implode(', ', $result) . ' trước' : 'vừa xong';
}

/**
 * (MỚI) Hàm kiểm tra đã mua khóa học chưa
 */
function has_bought_course($user_id, $course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM registrations WHERE student_email = (SELECT email FROM users WHERE id = ?) AND course_id = ? AND status = 'Đã xác nhận'");
    $stmt->execute([$user_id, $course_id]);
    return $stmt->fetch() ? true : false;
}
function text_to_list($text) {
    $text_trimmed = trim($text ?? '');
    if (empty($text_trimmed)) {
        return "<p>Chưa có thông tin.</p>";
    }
    
    $items = explode("\n", $text_trimmed);
    $html = '<ul class="feature-list">';
    foreach ($items as $item) {
        if (!empty(trim($item))) {
            // Icon SVG check mark
            $svg_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="color: var(--success-color); margin-right: 8px; flex-shrink: 0;"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>';
            $html .= '<li style="display:flex; align-items: flex-start; margin-bottom: 8px;">' . $svg_icon . '<span>' . h(trim($item)) . '</span></li>';
        }
    }
    $html .= '</ul>';
    return $html;
}
?>