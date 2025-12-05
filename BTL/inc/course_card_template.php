<?php
// Chuẩn bị dữ liệu
$image_url = !empty($course['image_url']) ? h($course['image_url']) : 'https://via.placeholder.com/300x200';
$link_detail = "course.php?id=" . h($course['id']);

// Tính toán rating (nếu chưa có thì random hoặc mặc định)
$rating = $course['rating'] ?? 5;
$rating_count = $course['rating_count'] ?? 0;
$students_count = $course['students_count'] ?? 0;

// Dữ liệu Like/Comment thực tế (nếu có cột này trong DB thì dùng, ko thì dùng fake data ở trên)
$likes_text = isset($course['likes_count']) ? $course['likes_count'] . ' thích' : $rating_count . ' đánh giá';
$comments_text = isset($course['comments_count']) ? $course['comments_count'] . ' bình luận' : '';
?>

<div class="course-card product-style">
    <a href="<?php echo $link_detail; ?>" class="course-link-wrapper">
        
        <div class="card-image-container">
            <img src="<?php echo $image_url; ?>" alt="<?php echo h($course['name']); ?>">
            <span class="discount-badge">Giảm 20%</span>
        </div>
        
        <div class="card-content">
            <h3 class="course-title"><?php echo h($course['name']); ?></h3>
            
            <div class="course-price-row">
                <span class="current-price"><?php echo number_format(h($course['fee']), 0, ',', '.'); ?>₫</span>
                <span class="old-price"><?php echo number_format(h($course['fee']) * 1.2, 0, ',', '.'); ?>₫</span>
            </div>

            <div class="course-rating-row">
                <div class="stars">
                    <?php 
                    for($i=1; $i<=5; $i++) {
                        if($i <= $rating) echo '<span class="star filled">★</span>';
                        else echo '<span class="star">☆</span>';
                    }
                    ?>
                </div>
                <span class="rating-count">(<?php echo $likes_text; ?> | <?php echo $comments_text; ?>)</span>
            </div>
            
            <div class="course-students">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332C3.154 12.014 3.001 12.754 3 13z"/></svg>
                <?php echo h($students_count); ?> học viên
            </div>
        </div>
    </a>

    <div class="add-cart-circle-form">
        <button type="button" class="btn-plus-cart" title="Thêm vào giỏ hàng" onclick="addToCart(<?php echo h($course['id']); ?>, this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
        </button>
    </div>
</div>