<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'inc/db.php';
require_once 'inc/functions.php';

// Lấy danh sách ID trong giỏ
$cart_ids = $_SESSION['cart'] ?? [];
$courses_in_cart = [];
$total_price = 0;

if (!empty($cart_ids)) {
    $in = str_repeat('?,', count($cart_ids) - 1) . '?';
    $sql = "SELECT * FROM courses WHERE id IN ($in)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($cart_ids));
    $courses_in_cart = $stmt->fetchAll();

    foreach ($courses_in_cart as $c) {
        $total_price += $c['fee'];
    }
}

require 'inc/header.php';
?>

<link rel="stylesheet" href="css/cart.css">

<div class="container">
    <h1 class="section-title">Giỏ hàng của bạn</h1>

    <?php if (empty($courses_in_cart)): ?>
        <div class="empty-cart">
            <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" alt="Empty Cart">
            <p>Giỏ hàng của bạn đang trống.</p>
            <a href="index.php" class="btn btn-primary">Tìm khóa học ngay</a>
        </div>
    <?php else: ?>
        <form id="cartForm" action="cart_action.php" method="POST">
            <input type="hidden" name="action" value="checkout">
            <div class="cart-container">

                <div class="cart-items">
                    <div class="cart-header">
                        Khóa học (<?php echo count($courses_in_cart); ?>)
                    </div>
                    <?php foreach ($courses_in_cart as $item): ?>
                        <?php
                        $img = !empty($item['image_url']) ? $item['image_url'] : 'https://via.placeholder.com/150';
                        ?>
                        <div class="cart-item">

                            <div class="item-image">
                                <img src="<?php echo h($img); ?>" alt="Ảnh khóa học">
                            </div>
                            <div class="item-details">
                                <h3><?php echo h($item['name']); ?></h3>
                                <p>Thời lượng: <?php echo h($item['duration']); ?></p>
                                <div class="item-price"><?php echo number_format($item['fee'], 0, ',', '.'); ?> VNĐ</div>
                            </div>

                            <div class="item-actions">
                                <input type="checkbox" name="selected_courses[]" value="<?php echo $item['id']; ?>"
                                    class="item-checkbox course-check" data-price="<?php echo $item['fee']; ?>" checked
                                    onclick="updateTotal()">

                                <a href="cart_action.php?action=remove&id=<?php echo $item['id']; ?>" class="btn-remove"
                                    onclick="return confirm('Xóa khóa học này?');">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        viewBox="0 0 16 16">
                                        <path
                                            d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                        <path
                                            d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4z" />
                                    </svg>
                                    Xóa
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($total_price, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <div class="summary-row">
                        <span>Giảm giá:</span>
                        <span>0 VNĐ</span>
                    </div>
                    <div class="summary-row total">
                        <span>Thành tiền:</span>
                        <span id="final-price"><?php echo number_format($total_price, 0, ',', '.'); ?> VNĐ</span>
                    </div>

                    <form id="checkoutForm" action="cart_action.php" method="POST">
                        <input type="hidden" name="action" value="checkout">
                        <button type="button" class="btn-checkout" onclick="openQR()">Thanh toán ngay</button>
                    </form>
                </div>
            </div>
        </form> <?php endif; ?>
</div>

<div class="qr-overlay" id="qrModal">
    <div class="qr-box">
        <div class="qr-header">
            <h3>Quét mã để thanh toán</h3>
            <span class="qr-close" onclick="closeQR()">×</span>
        </div>
        <div class="qr-body">
            <img src="https://img.vietqr.io/image/MB-123456789-compact2.jpg?amount=<?php echo $total_price; ?>&addInfo=Thanh toan khoa hoc&accountName=ADMIN"
                alt="Mã QR Thanh Toán" class="qr-image">

            <p class="qr-note">Tổng tiền cần thanh toán:</p>
            <div class="qr-amount"><?php echo number_format($total_price, 0, ',', '.'); ?> VNĐ</div>
        </div>
        <div class="qr-footer">
            <button class="btn-qr-cancel" onclick="closeQR()">Hủy</button>
            <button class="btn-qr-confirm" onclick="confirmPayment()">Đã chuyển</button>
        </div>
    </div>
</div>
<div style="height: 300px;">

</div>
<script>
    // Hàm mở Popup
    function openQR() {
        document.getElementById('qrModal').classList.add('show');
    }

    // Hàm đóng Popup (Hủy)
    function closeQR() {
        document.getElementById('qrModal').classList.remove('show');
    }

    // Hàm xác nhận (Đã chuyển) -> Submit form thật
    function confirmPayment() {
        document.getElementById('checkoutForm').submit();
    }
    function updateTotal() {
        const checkboxes = document.querySelectorAll('.course-check');
        let total = 0;
        let count = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                total += parseInt(cb.getAttribute('data-price'));
                count++;
            }
        });

        // 1. Cập nhật số tiền hiển thị
        const formattedMoney = total.toLocaleString('vi-VN') + ' VNĐ';
        document.getElementById('final-price').innerText = formattedMoney;

        // 2. Cập nhật ảnh QR theo số tiền mới (QUAN TRỌNG)
        // Thay thông tin STK của bạn vào đây
        const qrUrl = `https://img.vietqr.io/image/MB-123456789-compact2.jpg?amount=${total}&addInfo=Thanh toan&accountName=ADMIN`;
        const qrImg = document.getElementById('qrImage'); // Đảm bảo ảnh QR của bạn có id="qrImage"
        if (qrImg) qrImg.src = qrUrl;

        // 3. Cập nhật số tiền trong popup QR (nếu có element hiển thị)
        const qrAmount = document.querySelector('.qr-amount'); // Hoặc id tương ứng
        if (qrAmount) qrAmount.innerText = formattedMoney;
    }

    // Hàm xử lý khi bấm nút "Đã chuyển" trong Popup
    function confirmPayment() {
        // Submit form chứa các checkbox đã chọn
        document.getElementById('cartForm').submit();
    }
</script>
<?php require 'inc/footer.php'; ?>