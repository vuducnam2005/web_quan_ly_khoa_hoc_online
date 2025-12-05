<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/admin_header.php';

// 1. Truy vấn Top 10 người chi tiêu nhiều nhất
$sql = "
    SELECT 
        u.id, u.full_name, u.email, u.avatar,
        SUM(c.fee) as total_spent,
        COUNT(r.id) as total_courses
    FROM users u
    JOIN registrations r ON u.email = r.student_email
    JOIN courses c ON r.course_id = c.id
    WHERE r.status = 'Đã xác nhận'
    GROUP BY u.id
    ORDER BY total_spent DESC
    LIMIT 10
";
$stmt = $pdo->query($sql);
$top_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tách Top 3 và phần còn lại
$rank1 = $top_users[0] ?? null;
$rank2 = $top_users[1] ?? null;
$rank3 = $top_users[2] ?? null;
$rest_users = array_slice($top_users, 3);

function get_user_avatar($path) {
    if (!empty($path)) {
        // 1. Nếu chuỗi bắt đầu bằng "http" -> Là link online -> Dùng luôn
        if (strpos($path, 'http') === 0) {
            return $path;
        }
        
        // 2. Nếu là file trong máy (local) -> Thêm ../
        // Kiểm tra file có tồn tại không để tránh lỗi ảnh vỡ
        if (file_exists(__DIR__ . '/../' . $path)) {
            return '../' . $path;
        }
    }
    
    // 3. Không có gì hoặc file lỗi -> Ảnh mặc định
    return 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
}
?>

<link rel="stylesheet" href="../css/leaderboard.css">

<style>
    /* Link trên bục vinh quang (Màu trắng) */
    .podium-link {
        color: #fff;
        text-decoration: none;
        transition: opacity 0.2s;
    }
    .podium-link:hover {
        opacity: 0.8;
        text-decoration: underline;
    }

    /* Link trong danh sách (Màu đen) */
    .list-link {
        color: var(--text-dark);
        text-decoration: none;
        transition: color 0.2s;
    }
    .list-link:hover {
        color: var(--primary-color);
        text-decoration: underline;
    }
</style>

<h1>Bảng Xếp Hạng Chi Tiêu</h1>
<p style="color: #7f8c8d; margin-bottom: 2rem;">Vinh danh 10 học viên đóng góp nhiều nhất cho hệ thống.</p>

<div class="leaderboard-wrapper">
    
    <?php if (empty($top_users)): ?>
        <div class="stat-card" style="text-align: center; color: #777;">Chưa có dữ liệu chi tiêu nào.</div>
    <?php else: ?>

        <div class="podium-container">
            
            <div class="podium-item rank-2">
                <?php if ($rank2): ?>
                    <div class="podium-avatar-box">
                        <a href="user_detail.php?id=<?php echo $rank2['id']; ?>">
                            <img src="<?php echo get_user_avatar($rank2['avatar']); ?>" alt="Rank 2" class="podium-avatar">
                        </a>
                    </div>
                    <div class="podium-rank">
                        <div class="rank-num">2</div>
                        <div class="user-name">
                            <a href="user_detail.php?id=<?php echo $rank2['id']; ?>" class="podium-link">
                                <?php echo h($rank2['full_name']); ?>
                            </a>
                        </div>
                        <div class="total-spent"><?php echo number_format($rank2['total_spent'], 0, ',', '.'); ?>đ</div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="podium-item rank-1">
                <?php if ($rank1): ?>
                    <div class="podium-avatar-box">
                        <div class="crown-icon">👑</div>
                        <a href="user_detail.php?id=<?php echo $rank1['id']; ?>">
                            <img src="<?php echo get_user_avatar($rank1['avatar']); ?>" alt="Rank 1" class="podium-avatar">
                        </a>
                    </div>
                    <div class="podium-rank">
                        <div class="rank-num">1</div>
                        <div class="user-name">
                            <a href="user_detail.php?id=<?php echo $rank1['id']; ?>" class="podium-link">
                                <?php echo h($rank1['full_name']); ?>
                            </a>
                        </div>
                        <div class="total-spent"><?php echo number_format($rank1['total_spent'], 0, ',', '.'); ?>đ</div>
                        <div style="margin-top: 5px; font-size: 0.8rem;">(<?php echo $rank1['total_courses']; ?> khóa)</div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="podium-item rank-3">
                <?php if ($rank3): ?>
                    <div class="podium-avatar-box">
                        <a href="user_detail.php?id=<?php echo $rank3['id']; ?>">
                            <img src="<?php echo get_user_avatar($rank3['avatar']); ?>" alt="Rank 3" class="podium-avatar">
                        </a>
                    </div>
                    <div class="podium-rank">
                        <div class="rank-num">3</div>
                        <div class="user-name">
                            <a href="user_detail.php?id=<?php echo $rank3['id']; ?>" class="podium-link">
                                <?php echo h($rank3['full_name']); ?>
                            </a>
                        </div>
                        <div class="total-spent"><?php echo number_format($rank3['total_spent'], 0, ',', '.'); ?>đ</div>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <?php if (!empty($rest_users)): ?>
            <div class="list-container">
                <?php $rank = 4; ?>
                <?php foreach ($rest_users as $u): ?>
                    <div class="list-item">
                        <div class="list-rank"><?php echo $rank++; ?></div>
                        <div class="list-avatar">
                            <a href="user_detail.php?id=<?php echo $u['id']; ?>">
                                <img src="<?php echo get_user_avatar($u['avatar']); ?>" alt="Avatar">
                            </a>
                        </div>
                        <div class="list-info">
                            <h4>
                                <a href="user_detail.php?id=<?php echo $u['id']; ?>" class="list-link">
                                    <?php echo h($u['full_name']); ?>
                                </a>
                            </h4>
                            <small><?php echo $u['total_courses']; ?> khóa học đã mua</small>
                        </div>
                        <div class="list-money">
                            <?php echo number_format($u['total_spent'], 0, ',', '.'); ?> VNĐ
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>