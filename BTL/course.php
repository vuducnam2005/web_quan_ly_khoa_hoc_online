<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'inc/db.php';
require_once 'inc/functions.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    add_flash_message("Không tìm thấy khóa học.", 'error');
    header("Location: index.php");
    exit;
}

$image_url = !empty($course['image_url']) ? h($course['image_url']) : 'https://images.pexels.com/photos/1181263/pexels-photo-1181263.jpeg';

// Lấy danh sách bình luận
$sql_comments = "
    SELECT c.*, 
           u.full_name as user_name, u.avatar as user_avatar,
           a.username as admin_name
    FROM course_comments c
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN admins a ON c.admin_id = a.id
    WHERE c.course_id = ?
    ORDER BY c.created_at DESC
";
$stmt_comments = $pdo->prepare($sql_comments);
$stmt_comments->execute([$id]);
$all_comments = $stmt_comments->fetchAll();

$comments_tree = [];
foreach ($all_comments as $cmt) {
    if ($cmt['parent_id'] == null) {
        $comments_tree[$cmt['id']] = $cmt;
        $comments_tree[$cmt['id']]['replies'] = [];
    }
}
foreach ($all_comments as $cmt) {
    if ($cmt['parent_id'] != null && isset($comments_tree[$cmt['parent_id']])) {
        array_unshift($comments_tree[$cmt['parent_id']]['replies'], $cmt);
    }
}

// Kiểm tra Admin
$is_admin = isset($_SESSION['admin_id']);

require 'inc/header.php';
?>

<section class="course-detail-header"
    style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('<?php echo $image_url; ?>');">
    <div class="container">
        <?php if (!empty($course['category_name'])): ?>
            <span class="course-category-tag"><?php echo h($course['category_name']); ?></span>
        <?php endif; ?>
        <h1><?php echo h($course['name']); ?></h1>
        <div class="course-meta-detail">
            <strong>Học phí:</strong> <?php echo number_format(h($course['fee']), 0, ',', '.'); ?> VNĐ
            <span class="separator">|</span>
            <strong>Thời lượng:</strong> <?php echo h($course['duration']); ?>
        </div>
    </div>
</section>
<div class="container" style="margin-top: 3rem;">
    <div class="why-learn-section">
        <h2 class="why-learn-title">Tại sao nên học <?php echo h($course['name']); ?>?</h2>

        <div class="benefit-cards">
            <div class="benefit-card">
                <div class="benefit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path
                            d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0M2.04 4.326c.325 1.329 2.532 2.54 3.717 3.19.48.263.793.434.743.484-.08.08-.162.158-.242.234-.416.396-.787.749-.758 1.266.035.634.618.824 1.214.923 1.329.221 2.916.21 2.916.21l.002.21h-2.508a.75.75 0 0 0-.75.75v.696h2.347c-.042.618-.09 1.42-.09 2.703h1.606c0-1.308.031-2.088.074-2.703H13v-.696a.75.75 0 0 0-.75-.75H9.21c-.005-.356-.015-.826.175-1.236.043-.092.108-.182.193-.26.65-.586 1.77-1.596 1.77-2.78 0-1.11-.803-1.976-2.114-2.295C7.818 3.715 6.696 4.266 6.696 4.266c-1.004.534-1.205 1.662-1.205 1.662H3.98c0-1.38 1.26-2.86 2.487-3.254-.538-.35-1.546-.618-2.458-.348" />
                    </svg>
                </div>
                <h3>Được ưa chuộng nhất</h3>
                <p><strong><?php echo h($course['name']); ?></strong> là một trong những kỹ năng được yêu cầu nhiều nhất
                    trong thị trường lao động hiện nay, mở ra cơ hội toàn cầu.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path
                            d="M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v7.414a1 1 0 0 1-.293.707L12 13.914V16a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-2.086L1.293 11.121A1 1 0 0 1 1 10.414V3zm11 8.172V14a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-3.586l-3 2.758zM4 12.172l-3-2.758V14a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-2.828zM3 4a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H3z" />
                        <path d="M8 11a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zm0-1a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z" />
                    </svg>
                </div>
                <h3>Thu nhập hấp dẫn</h3>
                <p>Thành thạo <strong><?php echo h($course['name']); ?></strong> giúp bạn dễ dàng thăng tiến, đạt mức
                    lương cao và tự tin làm việc tại các công ty lớn.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path
                            d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1H4z" />
                    </svg>
                </div>
                <h3>Ứng dụng thực tế</h3>
                <p>Khóa học này cung cấp kiến thức thực chiến, giúp bạn áp dụng ngay vào dự án thực tế hoặc công việc
                    hàng ngày một cách hiệu quả.</p>
            </div>
        </div>
    </div>
</div>
<div class="course-detail-body">
    <div class="container">
        <div class="course-content-wrapper">

            <div class="course-content-main">

                <div class="interaction-bar">
                    <form action="comment_rating_action.php" method="POST" class="rating-form">
                        <input type="hidden" name="action" value="rate">
                        <input type="hidden" name="course_id" value="<?php echo $id; ?>">

                        <button type="submit" name="type" value="like" class="btn-interact btn-like">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M6.956 1.745C7.021.81 7.908.087 8.864.325l.261.066c.463.116.874.456 1.012.965.22.816.533 2.511.062 4.51a9.84 9.84 0 0 1 .443-.051c.713-.065 1.669-.072 2.516.21.518.173.994.681 1.2 1.273.184.532.16 1.162-.234 1.733.058.119.103.242.138.363.077.27.113.567.113.856 0 .289-.036.586-.113.856-.039.135-.09.273-.16.404.169.387.107.819-.003 1.148a3.163 3.163 0 0 1-.488.901c.054.152.076.312.076.465 0 .305-.089.625-.253.912C13.1 15.522 12.437 16 11.5 16H8c-.605 0-1.07-.081-1.466-.218a4.82 4.82 0 0 1-.97-.484l-.048-.03c-.504-.307-.999-.609-2.068-.722C2.682 14.464 2 13.846 2 13V9c0-.85.685-1.432 1.357-1.615.849-.232 1.574-.787 2.132-1.41.56-.627.914-1.28 1.039-1.639.199-.575.356-1.539.428-2.59z" />
                            </svg>
                            Thích (<?php echo $course['likes_count']; ?>)
                        </button>

                        <button type="submit" name="type" value="dislike" class="btn-interact btn-dislike">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M6.956 14.255c.065.936.952 1.659 1.908 1.42l.261-.065a1.378 1.378 0 0 0 1.012-.965c.22-.816.533-2.511.062-4.51.136.02.285.037.443.051.713.065 1.669.071 2.516-.211.518-.173.994-.68 1.2-1.272a1.896 1.896 0 0 0-.234-1.734c.058-.118.103-.242.138-.362.077-.27.113-.568.113-.856 0-.29-.036-.586-.113-.856a2.094 2.094 0 0 0-.16-.403c.169-.387.107-.82-.003-1.149a3.162 3.162 0 0 0-.488-.9c.054-.153.076-.313.076-.465a1.86 1.86 0 0 0-.253-.912C13.1.478 12.437 0 11.5 0H8c-.605 0-1.07.08-1.466.217a4.823 4.823 0 0 0-.97.485l-.048.029c-.504.308-.999.61-2.068.723C2.682 1.536 2 2.154 2 3v4c0 .85.685 1.433 1.357 1.616.849.232 1.574.787 2.132 1.41.56.626.914 1.28 1.039 1.638.199.575.356 1.54.428 2.591z" />
                            </svg>
                            Không thích (<?php echo $course['dislikes_count']; ?>)
                        </button>
                    </form>
                </div>

                <div class="course-section">
                    <h2>Mô tả chi tiết</h2>
                    <div class="course-full-description"><?php echo $course['full_description']; ?></div>
                </div>
                <div class="course-section">
                    <h2>Bạn sẽ học được gì?</h2>
                    <?php echo text_to_list($course['what_you_learn']); ?>
                </div>
                <div class="course-section">
                    <h2>Nội dung khóa học</h2>
                    <div class="syllabus-container">
                        <?php
                        // Xử lý hiển thị Lộ trình
                        $syllabus_text = $course['syllabus'] ?? '';
                        if (!empty($syllabus_text)) {
                            $lines = explode("\n", $syllabus_text);
                            echo '<div class="syllabus-list">';
                            foreach ($lines as $line) {
                                $line = trim($line);
                                if (empty($line)) continue;
                                
                                // Nếu dòng bắt đầu bằng "Chương", in đậm làm tiêu đề chương
                                if (strpos($line, 'Chương') === 0) {
                                    echo '<div class="syllabus-chapter">
                                            <span class="chapter-icon">📂</span> 
                                            <strong>' . h($line) . '</strong>
                                          </div>';
                                } else {
                                    // Các dòng bài học
                                    echo '<div class="syllabus-lesson">
                                            <span class="lesson-icon">🎥</span> 
                                            ' . h($line) . '
                                            <span class="lesson-time">10:00</span>
                                          </div>';
                                }
                            }
                            echo '</div>';
                        } else {
                            echo '<p>Chưa có thông tin lộ trình.</p>';
                        }
                        ?>
                    </div>
                </div>
                <div class="course-section" id="comments-section">
                    <h2>Bình luận & Hỏi đáp (<?php echo $course['comments_count']; ?>)</h2>

                    <div class="comment-box">
                        <form action="comment_rating_action.php" method="POST">
                            <input type="hidden" name="action" value="comment">
                            <input type="hidden" name="course_id" value="<?php echo $id; ?>">
                            <textarea name="content" class="comment-input" rows="3"
                                placeholder="Viết câu hỏi hoặc bình luận của bạn..." required></textarea>
                            <button type="submit" class="btn btn-primary btn-small" style="margin-top: 10px;">Gửi bình
                                luận</button>
                        </form>
                    </div>

                    <div class="comment-list">
                        <?php if (empty($comments_tree)): ?>
                            <p style="color: #777;">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
                        <?php else: ?>
                            <?php foreach ($comments_tree as $comment): ?>
                                <?php render_comment($comment, $is_admin); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <aside class="course-content-sidebar">
                <div class="register-box">
                    <button type="button" class="btn btn-primary btn-large"
                        style="width: 100%; display: flex; align-items: center; justify-content: center;"
                        onclick="addToCart(<?php echo h($course['id']); ?>, this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            viewBox="0 0 16 16" style="margin-right: 8px;">
                            <path
                                d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1z" />
                        </svg>
                        Thêm vào giỏ hàng
                    </button>
                </div>
            </aside>
        </div>
        <a href="index.php" class="back-link">&laquo; Quay lại danh sách</a>
    </div>
</div>

<?php
// Hàm hỗ trợ hiển thị bình luận
function render_comment($cmt, $is_admin, $is_reply = false)
{
    global $id;

    if (!empty($cmt['admin_id'])) {
        $name = "Quản trị viên";
        $avatar = "https://cdn-icons-png.flaticon.com/512/2304/2304226.png";
        $badge = '<span class="badge-admin">ADMIN</span>';
    } else {
        $name = h($cmt['user_name']);
        $avatar = !empty($cmt['user_avatar']) ? $cmt['user_avatar'] : 'https://source.unsplash.com/random/50x50?user';
        $badge = '';
    }

    $reply_class = $is_reply ? 'comment-reply' : '';
    ?>
    <div class="comment-item <?php echo $reply_class; ?>">
        <div class="comment-avatar">
            <img src="<?php echo $avatar; ?>" alt="<?php echo $name; ?>">
        </div>
        <div class="comment-body">
            <div class="comment-header">
                <span class="comment-author"><?php echo $name; ?>     <?php echo $badge; ?></span>
                <span class="comment-time"><?php echo time_elapsed_string($cmt['created_at']); ?></span>

                <?php if ($is_admin): ?>
                    <form action="comment_rating_action.php" method="POST" style="display:inline;"
                        onsubmit="return confirm('Xóa bình luận này?');">
                        <input type="hidden" name="action" value="delete_comment">
                        <input type="hidden" name="comment_id" value="<?php echo $cmt['id']; ?>">
                        <input type="hidden" name="course_id" value="<?php echo $id; ?>">
                        <button type="submit"
                            style="border:none; background:none; color:red; cursor:pointer; font-size:0.8rem; margin-left:10px;">Xóa</button>
                    </form>
                <?php endif; ?>

            </div>
            <div class="comment-content">
                <?php echo nl2br(h($cmt['content'])); ?>
            </div>

            <button class="btn-reply-toggle" onclick="toggleReplyForm(<?php echo $cmt['id']; ?>)">Trả lời</button>

            <div class="reply-form-container" id="reply-form-<?php echo $cmt['id']; ?>" style="display: none;">
                <form action="comment_rating_action.php" method="POST">
                    <input type="hidden" name="action" value="comment">
                    <input type="hidden" name="course_id" value="<?php echo $id; ?>">
                    <input type="hidden" name="parent_id" value="<?php echo $cmt['id']; ?>">
                    <textarea name="content" class="comment-input small" rows="2" placeholder="Viết câu trả lời..."
                        required></textarea>
                    <button type="submit" class="btn btn-primary btn-small" style="margin-top: 5px;">Gửi</button>
                </form>
            </div>
        </div>
    </div>

    <?php if (!empty($cmt['replies'])): ?>
        <?php foreach ($cmt['replies'] as $reply): ?>
            <?php render_comment($reply, $is_admin, true); ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php
}

require 'inc/footer.php';
?>

<script>
    function toggleReplyForm(id) {
        var form = document.getElementById('reply-form-' + id);
        if (form.style.display === 'none') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
</script>