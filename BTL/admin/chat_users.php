<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';

$selected_user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

// --- XỬ LÝ LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Admin trả lời (Có hỗ trợ Quote)
    if ($action === 'reply' && $selected_user_id > 0) {
        $reply = trim($_POST['reply'] ?? '');
        $reply_to_id = !empty($_POST['reply_to_id']) ? (int) $_POST['reply_to_id'] : null;

        if (!empty($reply)) {
            // Nếu là trả lời tin nhắn cũ, thêm trích dẫn vào đầu
            if ($reply_to_id) {
                $stmt_q = $pdo->prepare("SELECT message FROM messages WHERE id = ?");
                $stmt_q->execute([$reply_to_id]);
                $quoted_msg = $stmt_q->fetchColumn();
                if ($quoted_msg) {
                    // Format chuẩn để frontend tự parse
                    $reply = "[Trả lời: $quoted_msg]\n" . $reply;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO messages (user_id, admin_id, message, sender) VALUES (?, ?, ?, 'admin')");
            $stmt->execute([$selected_user_id, $_SESSION['admin_id'], $reply]);
        }
    }

    // 2. Xóa tin nhắn
    if ($action === 'delete_msg') {
        $msg_id = (int) $_POST['msg_id'];
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND user_id = ?");
        $stmt->execute([$msg_id, $selected_user_id]);
    }

    // 3. Sửa tin nhắn
    if ($action === 'edit_msg') {
        $msg_id = (int) $_POST['msg_id'];
        $new_content = trim($_POST['content']);
        $stmt = $pdo->prepare("UPDATE messages SET message = ? WHERE id = ? AND sender = 'admin'");
        $stmt->execute([$new_content, $msg_id]);
    }

    header("Location: chat_users.php?user_id=" . $selected_user_id);
    exit;
}

require_once __DIR__ . '/../inc/admin_header.php';

// Lấy danh sách user
$sql_users = "
    SELECT u.id, u.full_name, u.avatar, MAX(m.created_at) as last_msg_time
    FROM messages m
    JOIN users u ON m.user_id = u.id
    GROUP BY u.id
    ORDER BY last_msg_time DESC
";
$chat_users = $pdo->query($sql_users)->fetchAll();

// Lấy lịch sử chat
$chat_history = [];
$current_user_name = 'Học viên';
if ($selected_user_id > 0) {
  
    $stmt_u = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt_u->execute([$selected_user_id]);
    $current_user_name = $stmt_u->fetchColumn();

    $stmt_hist = $pdo->prepare("SELECT * FROM messages WHERE user_id = ? ORDER BY created_at ASC");
    $stmt_hist->execute([$selected_user_id]);
    $chat_history = $stmt_hist->fetchAll();
}
?>

<style>
    .chat-layout {
        display: flex;
        height: 75vh;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
    }

    .chat-sidebar {
        width: 300px;
        border-right: 1px solid #ddd;
        overflow-y: auto;
        background-color: #f8f9fa;
    }

    .user-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #eee;
        text-decoration: none;
        color: #333;
    }

    .user-item:hover,
    .user-item.active {
        background-color: #e3f2fd;
    }

    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .chat-messages-area {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
        background-color: #f9f9f9;
    }

    /* Style Tin nhắn */
    .msg-row {
        display: flex;
        gap: 10px;
        align-items: center;
        position: relative;
        width: 100%;
    }

    .msg-row.user {
        justify-content: flex-start;
    }

    .msg-row.admin {
        justify-content: flex-end;
    }

    .msg-bubble {
        max-width: 70%;
        padding: 10px 15px;
        border-radius: 18px;
        font-size: 0.95rem;
        line-height: 1.4;
        position: relative;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        word-wrap: break-word;
        width: fit-content;
    }

    .msg-row.user .msg-bubble {
        background: #fff;
        color: #333;
        border-bottom-left-radius: 4px;
    }

    .msg-row.admin .msg-bubble {
        background: #007bff;
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    /* Menu 3 chấm */
    .msg-actions {
        position: relative;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .msg-row:hover .msg-actions {
        opacity: 1;
    }

    .btn-msg-dots {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.2rem;
        color: #aaa;
        padding: 0 5px;
    }

    .msg-menu {
        position: absolute;
        bottom: 100%;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        display: none;
        z-index: 100;
        width: 90px;
    }

    .msg-menu.show {
        display: block;
    }

    .msg-row.admin .msg-menu {
        right: 0;
    }

    /* Admin menu bên phải */
    .msg-row.user .msg-menu {
        left: 0;
    }

    /* User menu bên trái */

    .msg-menu button {
        display: block;
        width: 100%;
        text-align: left;
        padding: 8px 10px;
        background: none;
        border: none;
        font-size: 0.85rem;
        cursor: pointer;
    }

    .msg-menu button:hover {
        background: #f5f5f5;
    }

    .msg-menu button.delete {
        color: red;
    }

    /* Quote Preview (Khung trả lời) */
    .reply-preview {
        padding: 8px 15px;
        background: #f1f1f1;
        border-left: 3px solid #007bff;
        margin-bottom: 0;
        border-radius: 4px 4px 0 0;
        font-size: 0.85rem;
        color: #555;
        display: none;
        justify-content: space-between;
        align-items: center;
    }

    .reply-content-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 300px;
    }

    .btn-close-reply {
        cursor: pointer;
        font-weight: bold;
        margin-left: 10px;
        color: #999;
    }

    .chat-reply-area {
        padding: 15px;
        background: #fff;
        border-top: 1px solid #ddd;
        display: flex;
        gap: 10px;
    }

    .chat-reply-area input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 20px;
    }

    /* Style hiển thị tin nhắn được trích dẫn */
    .quoted-msg {
        background: rgba(0, 0, 0, 0.05);
        border-left: 2px solid rgba(0, 0, 0, 0.2);
        padding: 5px 8px;
        margin-bottom: 5px;
        font-size: 0.85em;
        font-style: italic;
        border-radius: 4px;
        color: inherit;
        opacity: 0.8;
    }
</style>

<h1>Hỗ trợ Học viên</h1>

<div class="chat-layout">
    <div class="chat-sidebar">
        <?php foreach ($chat_users as $u): ?>
            <a href="chat_users.php?user_id=<?php echo $u['id']; ?>"
                class="user-item <?php echo ($u['id'] == $selected_user_id) ? 'active' : ''; ?>">
                <div>
                    <b><?php echo h($u['full_name']); ?></b><br><small><?php echo date('d/m H:i', strtotime($u['last_msg_time'])); ?></small>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="chat-main">
        <?php if ($selected_user_id > 0): ?>
            <div class="chat-messages-area" id="adminChatContent">
                <?php foreach ($chat_history as $msg): ?>
                    <?php
                    $row_class = ($msg['sender'] == 'admin') ? 'admin' : 'user';
                    // Parse quote
                    $content = htmlspecialchars($msg['message']);
                    $content = preg_replace('/^\[Trả lời: (.*?)\]\n/s', '<div class="quoted-msg">$1</div>', $content);
                    ?>
                    <div class="msg-row <?php echo $row_class; ?>">

                        <?php if ($row_class == 'admin'): ?>
                            <div class="msg-actions">
                                <button class="btn-msg-dots" onclick="toggleAdminMenu(this)">⋮</button>
                                <div class="msg-menu">
                                    <button
                                        onclick="editAdminMsg(<?php echo $msg['id']; ?>, `<?php echo htmlspecialchars(addslashes($msg['message'])); ?>`)">Sửa</button>
                                    <form method="POST" onsubmit="return confirm('Xóa?');">
                                        <input type="hidden" name="action" value="delete_msg">
                                        <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" class="delete">Xóa</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="msg-bubble"><?php echo nl2br($content); ?></div>

                        <?php if ($row_class == 'user'): ?>
                            <div class="msg-actions">
                                <button class="btn-msg-dots" onclick="toggleAdminMenu(this)">⋮</button>
                                <div class="msg-menu">
                                    <button
                                        onclick="replyToMsg(<?php echo $msg['id']; ?>, `<?php echo htmlspecialchars(addslashes($msg['message'])); ?>`)">↩
                                        Trả lời</button>
                                    <form method="POST" onsubmit="return confirm('Xóa tin này của user?');">
                                        <input type="hidden" name="action" value="delete_msg">
                                        <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" class="delete">Xóa</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>

            <div class="reply-preview" id="adminReplyPreview">
                <span id="adminReplyText" class="reply-content-text"></span>
                <span class="btn-close-reply" onclick="cancelAdminReply()">×</span>
            </div>

            <form method="POST" class="chat-reply-area" id="adminReplyForm">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="msg_id" id="editMsgId" value=""> <input type="hidden" name="reply_to_id"
                    id="replyToId" value=""> <input type="text" name="reply" id="adminInput" placeholder="Nhập tin nhắn..."
                    required autocomplete="off">
                <button type="submit" style="
    background: linear-gradient(135deg, #007bff, #0056b3); 
    color: white; 
    border: none; 
    padding: 10px 25px; 
    border-radius: 50px; 
    font-size: 14px; 
    font-weight: 600; 
    cursor: pointer; 
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3); 
    transition: all 0.2s ease;
    outline: none;"
                    onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 15px rgba(0, 123, 255, 0.4)';"
                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 10px rgba(0, 123, 255, 0.3)';">
                    Gửi ➤
                </button>
            </form>

            <script>
                var chatDiv = document.getElementById("adminChatContent");
                chatDiv.scrollTop = chatDiv.scrollHeight;

                function toggleAdminMenu(btn) {
                    document.querySelectorAll('.msg-menu').forEach(el => el.classList.remove('show'));
                    var menu = btn.nextElementSibling;
                    if (menu) menu.classList.toggle('show');
                }

                // Chức năng Trả lời
                function replyToMsg(id, content) {
                    // Reset form mode
                    document.querySelector('input[name="action"]').value = 'reply';
                    document.getElementById('editMsgId').value = '';

                    const shortContent = content.length > 50 ? content.substring(0, 50) + '...' : content;
                    document.getElementById('adminReplyText').innerText = "Đang trả lời: " + shortContent;
                    document.getElementById('replyToId').value = id;

                    document.getElementById('adminReplyPreview').style.display = 'flex';
                    document.getElementById('adminInput').focus();

                    // Đóng menu
                    document.querySelectorAll('.msg-menu').forEach(el => el.classList.remove('show'));
                }

                function cancelAdminReply() {
                    document.getElementById('replyToId').value = '';
                    document.getElementById('adminReplyPreview').style.display = 'none';
                    document.querySelector('input[name="action"]').value = 'reply';
                }

                // Chức năng Sửa (Giữ nguyên)
                function editAdminMsg(id, content) {
                    const cleanContent = content.replace(/^\[Trả lời: .*?\]\n/, '');
                    const newContent = prompt("Sửa tin nhắn:", cleanContent);
                    if (newContent && newContent !== content) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `<input type="hidden" name="action" value="edit_msg">
                                          <input type="hidden" name="msg_id" value="${id}">
                                          <input type="hidden" name="content" value="${newContent}">`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                }

                document.addEventListener('click', function (e) {
                    if (!e.target.matches('.btn-msg-dots')) {
                        document.querySelectorAll('.msg-menu').forEach(el => el.classList.remove('show'));
                    }
                });
            </script>
        <?php else: ?>
            <div style="display:flex;justify-content:center;align-items:center;height:100%;color:#888;">Chọn học viên để
                chat.</div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>