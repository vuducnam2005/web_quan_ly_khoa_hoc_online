<?php
// Kiểm tra biến trang để đóng thẻ div đúng cách
$current_page = basename($_SERVER['SCRIPT_NAME']);
$is_profile_page = ($current_page === 'profile.php');
$is_invoice_page = ($current_page === 'invoice.php');
?>

<?php if (!$is_profile_page && !$is_invoice_page): ?>
    </div> <?php endif; ?>

</main>

<footer class="site-footer">

    <div class="footer-cta">
        <div class="container">
            <h2>Tham gia Facebook group</h2>
            <a href="#" class="btn-cta-yellow">Tham gia ngay</a>
        </div>
    </div>

    <div class="footer-main">
        <div class="container">

            <div class="footer-grid">

                <div class="footer-col">
                    <h3>Dịch vụ</h3>
                    <ul>
                        <li><a href="#">Outsource</a></li>
                        <li><a href="#">Thiết kế</a></li>
                        <li><a href="#">Tư vấn</a></li>
                        <li><a href="#">Tuyển dụng</a></li>
                        <li><a href="#">Truyền thông</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h3>Đào tạo</h3>
                    <ul>
                        <li><a href="#">Roadmap</a></li>
                        <li><a href="#">Khoá học cơ bản</a></li>
                        <li><a href="#">Khoá học nâng cao</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h3>Giới thiệu</h3>
                    <ul>
                        <li><a href="#">Tiểu sử</a></li>
                        <li><a href="#">Mạng xã hội</a></li>
                        <li><a href="#">Liên hệ</a></li>
                    </ul>
                </div>

                <div class="footer-col newsletter-col">
                    <h3>Theo dõi để nhận tin tức mới nhất</h3>
                    <p>Cập nhật các khoá học mới qua email</p>
                    <form class="footer-subscribe-form">
                        <input type="email" placeholder="Email address" required>
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>

            <div class="footer-bottom-bar">
                <div class="copyright">
                    © 2025 - Designed by Ducnamcoder
                </div>
                <div class="social-icons">
                    <a href="https://www.facebook.com/nam.vu.996621" target="_blank"><svg
                            xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z" />
                        </svg></a>
                    <a href="https://www.facebook.com/nam.vu.996621" target="_blank"><svg
                            xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2z" />
                            <path fill-rule="evenodd" d="M9 3v10H8V3h1z" />
                            <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5V2.82z" />
                        </svg></a>
                    <a href="https://www.facebook.com/nam.vu.996621" target="_blank"><svg
                            xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z" />
                        </svg></a>
                    <a href="https://www.facebook.com/nam.vu.996621" target="_blank"><svg
                            xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z" />
                        </svg></a>
                </div>
            </div>
        </div>
    </div>
</footer>

</div> <button id="backToTop" title="Lên đầu trang">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
        <path fill-rule="evenodd"
            d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5a.5.5 0 0 0 .5.5z" />
    </svg>
</button>

<?php if (isset($_SESSION['user_id'])): ?>
    <link rel="stylesheet" href="css/chat.css">
    <div class="chat-toggle-btn" id="chatToggle">
        <span id="chatNotificationBadge" class="chat-badge" style="display: none;">0</span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
            <path
                d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z" />
        </svg>
    </div>
    <div class="chat-box-container" id="chatBox">
        <div class="chat-header">
            <h3>Hỗ trợ trực tuyến</h3>
            <span class="chat-close" id="chatClose">×</span>
        </div>
        <div class="chat-messages" id="chatContent"></div>
        <form class="chat-input-area" id="chatForm">
            <input type="text" id="chatInput" placeholder="Nhập tin nhắn..." autocomplete="off">
            <button type="submit">➤</button>
        </form>
    </div>
<?php endif; ?>

<script>
    let currentReplyId = null;

    document.addEventListener('DOMContentLoaded', function () {
        document.body.addEventListener('submit', function (e) {
            // Kiểm tra form có phải add to cart không
            if (e.target.tagName === 'FORM' && e.target.action.includes('cart_action.php')) {

                // Bỏ qua form checkout
                if (e.target.querySelector('input[name="action"][value="checkout"]')) return;

                e.preventDefault(); // CHẶN RELOAD
                const form = e.target;
                const formData = new FormData(form);
                formData.append('ajax', '1');

                // Tìm ảnh để bay
                let productImg = form.closest('.course-card')?.querySelector('img');
                if (!productImg) productImg = document.querySelector('.course-detail-header'); // Fallback trang chi tiết

                fetch('cart_action.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json()) // Nếu server trả về lỗi PHP text, dòng này sẽ throw error
                    .then(data => {
                        if (data.status === 'success') {
                            // 1. Bay
                            if (productImg) flyToCart(productImg);

                            // 2. Cập nhật số
                            setTimeout(() => { updateCartCount(data.count); }, 800);
                        } else if (data.status === 'error') {
                            if (data.message === 'login_required') {
                                if (confirm("Bạn cần đăng nhập để mua khóa học. Đăng nhập ngay?")) {
                                    window.location.href = 'user_login.php';
                                }
                            } else {
                                alert(data.message);
                                if (data.message && data.message.includes("bị khóa")) window.location.href = 'user_login.php';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi:', error);
                        // Nếu lỗi JSON parse (thường do PHP warning), reload trang để user thấy lỗi hoặc mua được theo cách cũ
                        // form.submit(); // Uncomment nếu muốn fallback về reload
                    });
            }
        });
        function checkNewMessages() {
            // Chỉ chạy khi chat đóng
            const chatBox = document.getElementById('chatBox');
            if (chatBox && chatBox.classList.contains('open')) return;

            const formData = new FormData();
            formData.append('action', 'check_new_user');

            fetch('chat_ajax.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    const badge = document.getElementById('chatNotificationBadge');
                    if (data.count > 0) {
                        badge.style.display = 'flex';
                        badge.innerText = data.count;
                    } else {
                        badge.style.display = 'none';
                    }
                });
        }

        // Sửa lại sự kiện Click mở chat để đánh dấu đã đọc
        if (chatToggle) {
            chatToggle.addEventListener('click', function () {
                chatBox.classList.add('open');
                chatToggle.style.display = 'none';

                // (MỚI) Ẩn badge và gọi API đánh dấu đã đọc
                document.getElementById('chatNotificationBadge').style.display = 'none';
                const formData = new FormData();
                formData.append('action', 'mark_read_user');
                fetch('chat_ajax.php', { method: 'POST', body: formData });

                loadMessages();
                setTimeout(() => { chatContent.scrollTop = chatContent.scrollHeight; }, 100);
            });
        }

        // Thêm vào setInterval để tự động check mỗi 5 giây
        setInterval(() => {
            if (chatBox && chatBox.classList.contains('open')) {
                loadMessages();
            } else {
                checkNewMessages(); // (MỚI) Nếu đóng thì check thông báo
            }
        }, 5000);
        // Hàm bay (Giữ nguyên)
        function flyToCart(sourceEl) {
            const cartIcon = document.getElementById('cart-icon-container');
            if (!cartIcon) return;

            const flyImg = document.createElement('div');
            flyImg.classList.add('flying-img');

            let bgUrl = '';
            if (sourceEl.tagName === 'IMG') bgUrl = sourceEl.src;
            else bgUrl = window.getComputedStyle(sourceEl).backgroundImage.slice(4, -1).replace(/["']/g, "");

            flyImg.style.backgroundImage = `url('${bgUrl}')`;
            flyImg.style.backgroundSize = 'cover';

            const startRect = sourceEl.getBoundingClientRect();
            flyImg.style.left = (startRect.left + startRect.width / 2 - 25) + 'px';
            flyImg.style.top = (startRect.top + startRect.height / 2 - 25) + 'px';

            document.body.appendChild(flyImg);

            const endRect = cartIcon.getBoundingClientRect();
            const endLeft = endRect.left + endRect.width / 2 - 5;
            const endTop = endRect.top + endRect.height / 2 - 5;

            // Bắt đầu bay sau 10ms
            setTimeout(() => {
                flyImg.style.left = endLeft + 'px';
                flyImg.style.top = endTop + 'px';
                flyImg.style.width = '10px';
                flyImg.style.height = '10px';
                flyImg.style.opacity = '0.5';
            }, 10);

            setTimeout(() => flyImg.remove(), 800);
        }

        // Hàm cập nhật số
        function updateCartCount(count) {
            const badge = document.getElementById('cart-count');
            if (badge) {
                badge.innerText = count;
                badge.classList.remove('pop-animate');
                void badge.offsetWidth;
                badge.classList.add('pop-animate');
            }
        }
    });


    // (Phần BackToTop và Cart giữ nguyên, chỉ update phần Chat)
    const sliderContainer = document.querySelector('.banner-slider-container');
    const sliderWrapper = document.querySelector('.slider-wrapper');

    // Chỉ chạy nếu tìm thấy banner trên trang
    if (sliderContainer && sliderWrapper) {
        const slides = document.querySelectorAll('.slide-item');
        const dots = document.querySelectorAll('.dot');

        let currentIndex = 0;
        const totalSlides = slides.length;
        let autoSlideInterval;
        const intervalTime = 3000; // 3 giây chuyển 1 lần

        // Hàm chuyển slide
        function goToSlide(index) {
            if (index < 0) index = totalSlides - 1;
            if (index >= totalSlides) index = 0;
            currentIndex = index;
            // Di chuyển wrapper
            sliderWrapper.style.transform = `translateX(-${currentIndex * 100}%)`;

            // Cập nhật chấm tròn active
            dots.forEach(dot => dot.classList.remove('active'));
            if (dots[currentIndex]) dots[currentIndex].classList.add('active');
        }

        // Tự động chạy
        function startAutoSlide() {
            stopAutoSlide();
            autoSlideInterval = setInterval(() => {
                goToSlide(currentIndex + 1);
            }, intervalTime);
        }

        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }

        // Kích hoạt chạy ngay lập tức
        startAutoSlide();

        // --- Xử lý Kéo chuột (Drag) để chuyển ảnh ---
        let isDragging = false;
        let startPos = 0;
        let currentTranslate = 0;
        let prevTranslate = 0;

        sliderContainer.addEventListener('mousedown', touchStart);
        sliderContainer.addEventListener('touchstart', touchStart);
        sliderContainer.addEventListener('mouseup', touchEnd);
        sliderContainer.addEventListener('mouseleave', () => { if (isDragging) touchEnd() });
        sliderContainer.addEventListener('touchend', touchEnd);
        sliderContainer.addEventListener('mousemove', touchMove);
        sliderContainer.addEventListener('touchmove', touchMove);

        function touchStart(event) {
            isDragging = true;
            stopAutoSlide(); // Dừng tự động khi đang kéo
            startPos = getPositionX(event);
            sliderContainer.style.cursor = 'grabbing';
        }

        function touchMove(event) {
            if (isDragging) {
                const currentPosition = getPositionX(event);
                const currentSlidePos = -(currentIndex * sliderContainer.offsetWidth);
                const diff = currentPosition - startPos;
                sliderWrapper.style.transform = `translateX(${currentSlidePos + diff}px)`;
            }
        }

        function touchEnd() {
            isDragging = false;
            sliderContainer.style.cursor = 'grab';
            const movedBy = getPositionX(event) - startPos;

            // Nếu kéo đủ dài (> 50px) thì đổi slide
            if (movedBy < -50) goToSlide(currentIndex + 1);
            else if (movedBy > 50) goToSlide(currentIndex - 1);
            else goToSlide(currentIndex); // Trả về vị trí cũ

            startAutoSlide(); // Chạy lại tự động
        }

        function getPositionX(event) {
            return event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;
        }

        // Hàm global để gọi từ nút chấm tròn (onclick="currentSlide(n)")
        window.currentSlide = function (n) {
            goToSlide(n);
            stopAutoSlide();
            startAutoSlide();
        }
    }
    const chatToggle = document.getElementById('chatToggle');
    const chatBox = document.getElementById('chatBox');
    const chatClose = document.getElementById('chatClose');
    const chatContent = document.getElementById('chatContent');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');

    if (chatToggle) {
        // Mở chat
        chatToggle.addEventListener('click', function () {
            chatBox.classList.add('open');
            chatToggle.style.display = 'none';
            loadMessages();
        });

        // Đóng chat
        chatClose.addEventListener('click', function () {
            chatBox.classList.remove('open');
            chatToggle.style.display = 'flex';
        });

        // Tạo vùng hiển thị Quote
        const replyPreviewDiv = document.createElement('div');
        replyPreviewDiv.className = 'reply-preview';
        replyPreviewDiv.style.display = 'none'; // Mặc định ẩn
        replyPreviewDiv.innerHTML = '<span id="replyText"></span> <span class="btn-close-reply" onclick="cancelReply()">×</span>';
        chatForm.parentElement.insertBefore(replyPreviewDiv, chatForm);

        // Tải tin nhắn
        window.loadMessages = function () {
            const formData = new FormData();
            formData.append('action', 'load');
            fetch('chat_ajax.php', { method: 'POST', body: formData })
                .then(res => res.text())
                .then(html => {
                    chatContent.innerHTML = html;
                    // Tự động cuộn xuống dưới cùng
                    // chatContent.scrollTop = chatContent.scrollHeight; 
                });
        }

        // Gửi tin
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const msg = chatInput.value.trim();
            if (!msg) return;

            const formData = new FormData();
            formData.append('action', 'send');
            formData.append('message', msg);
            if (currentReplyId) {
                formData.append('reply_to_id', currentReplyId);
            }

            // Hiện tạm để thấy nhanh
            let replyHtml = '';
            if (currentReplyId) {
                const replyText = document.getElementById('replyText').innerText;
                replyHtml = `<div class="reply-preview" style="display:block; margin-bottom:5px; font-style:italic; border-left:2px solid #fff; padding-left:5px; font-size:0.85em;">${replyText}</div>`;
            }
            const tempMsg = `<div class="msg-row user"><div class="msg-actions"></div><div class="message-bubble msg-user">${replyHtml}${msg}</div></div>`;
            chatContent.insertAdjacentHTML('beforeend', tempMsg);
            chatContent.scrollTop = chatContent.scrollHeight;

            chatInput.value = '';
            cancelReply();

            fetch('chat_ajax.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') loadMessages();
                });
        });

        // Auto refresh
        setInterval(() => {
            if (chatBox.classList.contains('open')) loadMessages();
        }, 5000);
    }

    // BẮT SỰ KIỆN CLICK ĐỂ ĐÓNG MENU KHI BẤM RA NGOÀI
    document.addEventListener('click', function (e) {
        if (!e.target.matches('.btn-msg-dots')) {
            document.querySelectorAll('.msg-menu').forEach(el => el.classList.remove('show'));
        }
    });


    // === CÁC HÀM GLOBAL (Được gọi từ HTML onclick) ===

    // 1. Bật/Tắt Menu
    function toggleMsgMenu(btn) {
        // Đóng menu khác
        document.querySelectorAll('.msg-menu').forEach(el => el.classList.remove('show'));
        // Tìm menu ngay kế bên nút bấm
        const menu = btn.nextElementSibling;
        if (menu) {
            menu.classList.toggle('show');
        }
        // Ngăn chặn sự kiện nổi bọt để không bị document click đóng ngay lập tức
        event.stopPropagation();
    }

    // 2. Xóa tin nhắn
    function deleteMessage(id) {
        if (!confirm('Bạn muốn xóa tin nhắn này?')) return;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('msg_id', id);
        fetch('chat_ajax.php', { method: 'POST', body: formData })
            .then(() => loadMessages());
    }

    // 3. Sửa tin nhắn
    function editMessage(id, oldContent) {
        // Lọc bỏ phần [Trả lời: ...]
        const cleanContent = oldContent.replace(/^\[Trả lời: .*?\]\n/, '');
        const newContent = prompt("Sửa tin nhắn:", cleanContent);
        if (newContent !== null && newContent.trim() !== "") {
            const formData = new FormData();
            formData.append('action', 'edit');
            formData.append('msg_id', id);
            formData.append('content', newContent);
            fetch('chat_ajax.php', { method: 'POST', body: formData })
                .then(() => loadMessages());
        }
    }

    // 4. Trả lời
    function replyMessage(id, content) {
        const shortContent = content.length > 40 ? content.substring(0, 40) + '...' : content;
        document.getElementById('replyText').innerText = "Trả lời: " + shortContent;
        document.querySelector('.reply-preview').style.display = 'flex';
        currentReplyId = id;
        document.getElementById('chatInput').focus();
        // Đóng menu
        document.querySelectorAll('.msg-menu').forEach(el => el.classList.remove('show'));
    }

    // 5. Hủy trả lời
    function cancelReply() {
        currentReplyId = null;
        const preview = document.querySelector('.reply-preview');
        if (preview) preview.style.display = 'none';
    }
    function addToCart(courseId, btnElement) {

        // 1. Tìm ảnh để làm hiệu ứng bay
        let productImg = null;
        const parentCard = btnElement.closest('.course-card');
        if (parentCard) {
            productImg = parentCard.querySelector('img');
        } else {
            // Nếu đang ở trang chi tiết
            const headerBg = document.querySelector('.course-detail-header');
            if (headerBg) productImg = headerBg;

        }

        // 2. Gửi dữ liệu ngầm (AJAX)
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('course_id', courseId);
        formData.append('ajax', '1');

        fetch('cart_action.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // A. Chạy hiệu ứng bay
                    if (productImg && typeof flyToCart === 'function') {
                        flyToCart(productImg);
                    }
                    // B. Cập nhật số lượng
                    setTimeout(() => {
                        const badge = document.getElementById('cart-count');
                        if (badge) badge.innerText = data.count;
                    }, 800);
                    window.location.href = "cart.php";
                } else if (data.status === 'error') {
                    if (data.message === 'login_required') {
                        if (confirm("Bạn cần đăng nhập để mua khóa học. Đăng nhập ngay?")) {
                            window.location.href = 'user_login.php';
                        }
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => console.error('Lỗi:', error));
        
    }
</script>
</body>

</html>