<?php
require 'inc/db.php';
require_once 'inc/functions.php'; 
require 'inc/header.php';


// 1. Lấy từ khóa tìm kiếm
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// 2. Logic xử lý dữ liệu
if (!empty($search_query)) {
    // === CHẾ ĐỘ TÌM KIẾM ===
    $sql = "SELECT * FROM courses 
            WHERE name LIKE ? 
            OR short_description LIKE ? 
            OR full_description LIKE ? 
            ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $search_param = "%" . $search_query . "%";
    $stmt->execute([$search_param, $search_param, $search_param]);
    $search_results = $stmt->fetchAll();
    
    $is_searching = true;

} else {
    // === CHẾ ĐỘ MẶC ĐỊNH ===
    $is_searching = false;
    
    $stmt_cats = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $stmt_cats->fetchAll();

    $stmt_courses = $pdo->query("SELECT * FROM courses ORDER BY id ASC");
    $all_courses = $stmt_courses->fetchAll();

    $courses_by_category = [];
    foreach ($all_courses as $course) {
        $cat_id = $course['category_id'] ?? 0;
        $courses_by_category[$cat_id][] = $course;
    }
    
    $selected_cat_id = isset($_GET['category']) ? (int)$_GET['category'] : ($categories[0]['id'] ?? 0);
}
?>

<div class="banner-slider-container">
    <div class="slider-wrapper">
        <div class="slide-item">
            <img src="assets/images/banners/banner1.jpg" alt="Banner 1">
        </div>

        <div class="slide-item">
            <img src="assets/images/banners/banner2.jpg" alt="Banner 2">
        </div>

        <div class="slide-item">
            <img src="assets/images/banners/banner3.jpg" alt="Banner 3">
        </div>
        <div class="slide-item">
            <img src="assets/images/banners/banner4.jpg" alt="Banner 3">
        </div>
        <div class="slide-item">
            <img src="assets/images/banners/banner5.jpg" alt="Banner 3">
        </div>
        </div>

    <div class="slider-dots">
        <span class="dot" onclick="currentSlide(0)"></span> 
        <span class="dot" onclick="currentSlide(1)"></span> 
        <span class="dot" onclick="currentSlide(2)"></span> 
        <span class="dot" onclick="currentSlide(3)"></span> 
        <span class="dot" onclick="currentSlide(4)"></span> 
       
    </div>
</div>

<div class="search-bar-top-wrapper" id="search-bar-anchor">
    <div class="container">
        <form action="index.php" method="GET" class="search-form-top">
            <input type="text" name="q" class="search-input-top" 
                   placeholder="Bạn muốn học gì hôm nay? " 
                   value="<?php echo h($search_query); ?>">
            <button type="submit" class="search-btn-top">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                Tìm kiếm
            </button>
        </form>
    </div>
</div>


<section class="course-grid-container" id="course-list-section">
    <div class="container">
        
        <?php if ($is_searching): ?>
            <div class="search-results-header" style="text-align: center; margin-bottom: 2.5rem;">
                <h1 class="section-title">Kết quả tìm kiếm cho: "<?php echo h($search_query); ?>"</h1>
                <p style="color: var(--secondary-color); font-size: 1.05rem; margin-bottom: 1rem;">
                    Tìm thấy <strong style="color: var(--primary-color);"><?php echo count($search_results); ?></strong> khóa học phù hợp.
                </p>
                <a href="index.php" class="clear-search-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.854 2.146a.5.5 0 0 1 0 .708L4.707 12.146a.5.5 0 0 1-.708 0L.146 8.207a.5.5 0 1 1 .708-.708L4 10.293 13.146 2.854a.5.5 0 0 1 .708 0z"/></svg>
                    Quay lại danh sách đầy đủ
                </a>
            </div>

            <div class="course-list">
                <?php if (empty($search_results)): ?>
                    <div style="text-align: center; width: 100%; grid-column: 1 / -1;">
                        <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" alt="Không tìm thấy" style="width: 100px; opacity: 0.5; margin-bottom: 1rem;">
                        <p style="font-size: 1.1rem; color: var(--secondary-color);">Rất tiếc, không tìm thấy khóa học nào phù hợp với từ khóa này.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($search_results as $course): ?>
                        <?php include 'inc/course_card_template.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="category-nav-wrapper" style="position: static; box-shadow: none; border: none; margin-bottom: 2rem; padding: 0;">
                <nav class="category-nav">
                    <ul>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="index.php?category=<?php echo $cat['id']; ?>#course-list-section"
                                   class="<?php echo ($cat['id'] == $selected_cat_id) ? 'active' : ''; ?>">
                                    <?php echo h($cat['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>

            <?php
                $current_cat_courses = $courses_by_category[$selected_cat_id] ?? [];
                $current_cat_info = null;
                foreach ($categories as $c) { if ($c['id'] == $selected_cat_id) $current_cat_info = $c; }
            ?>

            <?php if ($current_cat_info): ?>
                <div style="text-align: center; margin-bottom: 2rem;">
                    <p class="category-description"><?php echo h($current_cat_info['description']); ?></p>
                </div>
            <?php endif; ?>

            <div class="course-list">
                <?php if (empty($current_cat_courses)): ?>
                    <p style="text-align: center; width: 100%;">Chưa có khóa học nào trong danh mục này.</p>
                <?php else: ?>
                    <?php foreach ($current_cat_courses as $course): ?>
                        <?php include 'inc/course_card_template.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php endif; ?>
        
    </div>
</section>
<div style="height: 150px;">

</div>
<?php require 'inc/footer.php'; ?>