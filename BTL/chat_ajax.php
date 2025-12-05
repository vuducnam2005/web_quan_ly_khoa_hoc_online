<?php
session_start();
require 'inc/db.php';

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) { echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']); exit; }

try {
    switch ($action) {
        // --- GỬI TIN NHẮN ---
        case 'send':
            $message = trim($_POST['message'] ?? '');
            $reply_to_id = !empty($_POST['reply_to_id']) ? (int)$_POST['reply_to_id'] : null;
            
            if (!empty($message)) {
                if ($reply_to_id) {
                    $stmt_q = $pdo->prepare("SELECT message FROM messages WHERE id = ?");
                    $stmt_q->execute([$reply_to_id]);
                    $quoted_msg = $stmt_q->fetchColumn();
                    if ($quoted_msg) {
                        $message = "[Trả lời: $quoted_msg]\n" . $message;
                    }
                }

                $stmt = $pdo->prepare("INSERT INTO messages (user_id, message, sender) VALUES (?, ?, 'user')");
                $stmt->execute([$user_id, $message]);
                echo json_encode(['status' => 'success']);
            }
            break;

        // --- SỬA TIN NHẮN ---
        case 'edit':
            $msg_id = (int)$_POST['msg_id'];
            $new_content = trim($_POST['content']);
            // Chỉ sửa được tin của chính mình
            $stmt = $pdo->prepare("UPDATE messages SET message = ? WHERE id = ? AND user_id = ? AND sender = 'user'");
            $stmt->execute([$new_content, $msg_id, $user_id]);
            echo json_encode(['status' => 'success']);
            break;

        // --- XÓA TIN NHẮN ---
        case 'delete':
            $msg_id = (int)$_POST['msg_id'];
            // Chỉ xóa được tin của chính mình
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND user_id = ? AND sender = 'user'");
            $stmt->execute([$msg_id, $user_id]);
            echo json_encode(['status' => 'success']);
            break;
        case 'check_new_user':
            // Đếm tin nhắn gửi từ ADMIN mà User chưa đọc
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE user_id = ? AND sender = 'admin' AND is_read = 0");
            $stmt->execute([$user_id]);
            $count = $stmt->fetchColumn();
            echo json_encode(['status' => 'success', 'count' => $count]);
            exit;

        // --- (MỚI) ĐÁNH DẤU ĐÃ ĐỌC (Khi mở hộp chat) ---
        case 'mark_read_user':
            // User mở chat -> Đánh dấu tin của Admin là đã đọc
            $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender = 'admin'");
            $stmt->execute([$user_id]);
            echo json_encode(['status' => 'success']);
            exit;
        // --- TẢI TIN NHẮN ---
        case 'load':
            $stmt = $pdo->prepare("SELECT * FROM messages WHERE user_id = ? ORDER BY created_at ASC");
            $stmt->execute([$user_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $html = '';
            if (empty($messages)) {
                $html .= '<div class="msg-row admin"><div class="message-bubble msg-admin">Chào bạn! Admin có thể giúp gì?</div></div>';
            }
            
            foreach ($messages as $msg) {
                $is_user = ($msg['sender'] == 'user');
                $class = $is_user ? 'msg-user' : 'msg-admin';
                $row_class = $is_user ? 'user' : 'admin';
                $time = date('H:i', strtotime($msg['created_at']));
                
                // Xử lý hiển thị tin nhắn trả lời
                $content = htmlspecialchars($msg['message']);
                $content = preg_replace('/^\[Trả lời: (.*?)\]\n/s', '<div class="reply-preview" style="display:block; margin-bottom:5px; font-style:italic; border-left:2px solid #fff; padding-left:5px; font-size:0.85em;">$1</div>', $content);

                // HTML cho Menu 3 chấm (Chỉ hiện cho tin nhắn của User)
                $menu_html = '';
                if ($is_user) {
                    $menu_html = '
                    <div class="msg-actions">
                        <button class="btn-msg-dots" type="button" onclick="toggleMsgMenu(this)">⋮</button>
                        <div class="msg-menu">
                            <button type="button" onclick="editMessage('.$msg['id'].', `'.htmlspecialchars(addslashes($msg['message'])).'`)">✎ Sửa</button>
                            <button type="button" class="delete-opt" onclick="deleteMessage('.$msg['id'].')">🗑 Xóa</button>
                        </div>
                    </div>';
                } 
                // Nút trả lời cho Admin (nếu muốn user reply admin thì thêm ở đây)
                else {
                     $menu_html = '
                    <div class="msg-actions">
                        <button class="btn-msg-dots" type="button" onclick="toggleMsgMenu(this)">⋮</button>
                        <div class="msg-menu">
                             <button type="button" onclick="replyMessage('.$msg['id'].', `'.htmlspecialchars(addslashes($msg['message'])).'`)">↩ Trả lời</button>
                        </div>
                    </div>';
                }

                // Cấu trúc HTML: User bên phải (Menu - Bubble), Admin bên trái (Bubble - Menu)
                $html .= '<div class="msg-row ' . $row_class . '">';
                
                if ($is_user) {
                    // User: Menu bên trái bong bóng
                    $html .= $menu_html;
                    $html .= '<div class="message-bubble ' . $class . '">' . nl2br($content) . '<span class="msg-time">' . $time . '</span></div>';
                } else {
                    // Admin: Bong bóng trước, Menu sau (nếu có)
                    $html .= '<div class="message-bubble ' . $class . '">' . nl2br($content) . '<span class="msg-time">' . $time . '</span></div>';
                    $html .= $menu_html;
                }
                
                $html .= '</div>';
            }
            echo $html;
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>