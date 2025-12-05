# 🎓 Hệ Thống Quản Lý & Bán Khóa Học Online (E-Learning Platform)

Dự án website bán khóa học trực tuyến được xây dựng bằng **PHP thuần** và **MySQL**, tập trung vào trải nghiệm người dùng (UX) mượt mà với các hiệu ứng AJAX, animation và giao diện quản trị mạnh mẽ.

---

## 🚀 Tính Năng Nổi Bật

### 1. Dành cho Người dùng (Khách & Học viên)
* **Trang chủ hiện đại:** Banner slider tự động chạy, hiển thị khóa học theo danh mục (Tab), hiệu ứng hover đẹp mắt.
* **Tìm kiếm thông minh (Live Search):** Gợi ý khóa học ngay lập tức khi gõ từ khóa.
* **Đa ngôn ngữ:** Tích hợp Google Translate (Anh, Việt, Hàn, Nhật, Trung) với giao diện tùy chỉnh.
* **Giỏ hàng AJAX:**
    * Thêm vào giỏ hàng không cần tải lại trang.
    * Hiệu ứng ảnh khóa học "bay" vào giỏ hàng.
    * Cập nhật số lượng tức thì.
* **Thanh toán mô phỏng:**
    * Chọn nhiều khóa học để thanh toán cùng lúc (Checkbox).
    * Hiển thị Popup mã QR động theo số tiền.
* **Hồ sơ Học viên (Profile):**
    * Quản lý thông tin cá nhân, thay đổi mật khẩu.
    * Tự động tạo **Mã học viên** (VD: `177102xxxx`).
    * Upload ảnh đại diện (Avatar) hoặc dùng Avatar tự động theo tên.
    * Xem lịch sử đơn hàng và trạng thái duyệt.
* **Học tập & Tương tác:**
    * Xem lộ trình học chi tiết (Syllabus) cho từng khóa.
    * **Tải tài liệu** (ZIP/PDF) cho các khóa học đã mua.
    * **Bình luận & Đánh giá:** Like/Dislike, Bình luận, Trả lời bình luận (Nested Reply).
    * **Chat Hỗ trợ:** Chat trực tiếp với Admin, có menu xóa/sửa tin nhắn.

### 2. Dành cho Quản trị viên (Admin)
* **Dashboard:** Thống kê doanh thu, số lượng đơn hàng, biểu đồ trực quan.
* **Quản lý Khóa học:**
    * Thêm/Sửa/Xóa khóa học.
    * Upload ảnh bìa và **File tài liệu** (ZIP/PDF).
    * Quản lý lộ trình học.
* **Quản lý Đơn hàng:** Duyệt đơn, Hủy đơn (Số lượng học viên tự động tăng khi duyệt).
* **Quản lý Người dùng:**
    * Xem chi tiết lịch sử mua hàng, tổng tiền đã chi.
    * Khóa/Mở khóa tài khoản.
    * Cấp lại mật khẩu.
* **Hệ thống Chat & Phản hồi:**
    * Xem danh sách người dùng cần hỗ trợ.
    * Trả lời tin nhắn, xóa tin nhắn vi phạm.
* **Bảng Xếp hạng (Leaderboard):** Top 10 học viên chi tiêu nhiều nhất với giao diện bục vinh quang (Podium).

---

## 🛠️ Công Nghệ Sử Dụng

* **Backend:** PHP (Native), PDO (Kết nối CSDL an toàn, chống SQL Injection).
* **Database:** MySQL.
* **Frontend:** HTML5, CSS3 (Flexbox, Grid, Animation), JavaScript (Vanilla JS, Fetch API).
* **Server:** Apache (XAMPP / WampServer).

---

## ⚙️ Hướng Dẫn Cài Đặt

### Bước 1: Chuẩn bị môi trường
1.  Cài đặt **XAMPP** hoặc **WampServer**.
2.  Khởi động Apache và MySQL.

### Bước 2: Cài đặt mã nguồn
1.  Tải source code về và giải nén.
2.  Copy thư mục dự án vào thư mục gốc của server (ví dụ: `C:\wamp64\www\BTL` hoặc `C:\xampp\htdocs\BTL`).
3.  Tạo các thư mục upload nếu chưa có:
    * `uploads/courses/`
    * `uploads/materials/`

### Bước 3: Cấu hình Cơ sở dữ liệu
1.  Truy cập **phpMyAdmin** (thường là `http://localhost/phpmyadmin`).
2.  Tạo một CSDL mới tên là: `course_db`.
3.  Nhấn vào tab **Nhập (Import)** và chọn file `schema.sql` (nằm trong thư mục gốc của dự án).
4.  *(Tùy chọn)* Chạy các thủ tục (Stored Procedure) trong file SQL để tạo dữ liệu giả (Buff user, đơn hàng, bình luận).

### Bước 4: Cấu hình kết nối
Mở file `inc/db.php` và chỉnh sửa thông tin nếu cần:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'course_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mặc định XAMPP/WAMP là rỗng
Bước 5: Chạy dự án
Mở trình duyệt và truy cập: http://localhost/BTL

📂 Cấu trúc Thư mục
BTL/  (Thư mục gốc dự án)
│
├── admin/                      # KHU VỰC QUẢN TRỊ (ADMIN)
│   ├── chat_users.php          # Trang chat hỗ trợ với học viên
│   ├── course_action.php       # Xử lý Thêm/Sửa/Xóa khóa học & file tài liệu
│   ├── courses.php             # Giao diện Quản lý khóa học
│   ├── dashboard.php           # Trang tổng quan (Thống kê nhanh)
│   ├── index.php               # Trang đăng nhập dành riêng cho Admin (nếu có)
│   ├── logout.php              # Xử lý đăng xuất Admin
│   ├── reg_action.php          # Xử lý Duyệt/Hủy đơn hàng
│   ├── registrations.php       # Giao diện Quản lý đơn hàng
│   ├── stats.php               # Trang Thống kê doanh thu chi tiết
│   ├── top_spenders.php        # Bảng xếp hạng Top chi tiêu (Podium)
│   ├── user_action.php         # Xử lý Khóa/Mở khóa/Xóa người dùng
│   ├── user_detail.php         # Trang xem chi tiết hồ sơ 1 người dùng
│   └── users.php               # Giao diện Quản lý người dùng
│
├── css/                        # CÁC FILE GIAO DIỆN (STYLES)
│   ├── admin.css               # Giao diện chung cho Admin Panel
│   ├── base.css                # Các thiết lập gốc (màu sắc, font chữ, reset)
│   ├── cart.css                # Giao diện Giỏ hàng & Popup QR
│   ├── chat.css                # Giao diện Bong bóng chat & Nút chat tròn
│   ├── invoice.css             # Giao diện Hóa đơn (để in ấn)
│   ├── leaderboard.css         # Giao diện Bảng xếp hạng (Bục vinh quang)
│   ├── profile.css             # Giao diện Trang cá nhân (Sidebar, Tabs)
│   └── public.css              # Giao diện Trang chủ, Banner, Danh sách khóa học
│
├── inc/                        # CÁC FILE DÙNG CHUNG (INCLUDES)
│   ├── admin_footer.php        # Chân trang Admin (chứa script admin)
│   ├── admin_header.php        # Đầu trang Admin (chứa Menu dọc Sidebar)
│   ├── auth.php                # Kiểm tra đăng nhập Admin (bảo vệ trang)
│   ├── course_card_template.php# Đoạn HTML mẫu thẻ khóa học (dùng cho Search/List)
│   ├── db.php                  # Kết nối CSDL & Cấu hình múi giờ
│   ├── footer.php              # Chân trang User (chứa JS Chat, Cart, Banner...)
│   ├── functions.php           # Các hàm hỗ trợ (h, flash_message, time_elapsed...)
│   └── header.php              # Đầu trang User (Menu, Nút giỏ hàng, Google Translate)
│
├── uploads/                    # THƯ MỤC LƯU TRỮ FILE
│   ├── courses/                # Chứa ảnh bìa khóa học (.jpg, .png)
│   ├── materials/              # Chứa file tài liệu khóa học (.zip, .pdf)
│   └── (các file ảnh avatar)   # Ảnh đại diện user nằm ngay trong uploads/
│
├── cart.php                    # Giao diện Giỏ hàng (Checkbox, Tổng tiền)
├── cart_action.php             # Xử lý logic Giỏ hàng (Thêm, Xóa, Thanh toán)
├── chat_ajax.php               # API xử lý gửi/nhận tin nhắn Chat
├── comment_rating_action.php   # Xử lý Bình luận, Like, Dislike
├── course.php                  # Trang Chi tiết khóa học
├── index.php                   # Trang chủ (Banner, Tìm kiếm, Danh sách khóa)
├── invoice.php                 # Trang xem Hóa đơn chi tiết
├── login_action.php            # Xử lý đăng nhập (cho cả User và Admin)
├── profile.php                 # Trang cá nhân (Thông tin, Đổi pass, Lịch sử)
├── README.md                   # File hướng dẫn sử dụng
├── schema.sql                  # File chứa câu lệnh tạo CSDL
├── search_ajax.php             # API xử lý Tìm kiếm gợi ý (Live Search)
├── user_login.php              # Form đăng nhập
├── user_logout.php             # Xử lý đăng xuất User
└── user_register.php           # Form đăng ký tài khoản mới
🔑 Tài Khoản Demo
1. Quản trị viên (Admin):

Username: admin

Password: 123456 (Hoặc hash trong CSDL)

2. Người dùng (User):

Bạn có thể tự Đăng ký tài khoản mới ngoài trang chủ.

Hoặc sử dụng các tài khoản được tạo tự động trong CSDL.

🛡️ Lưu ý Bảo mật & Vận hành
File inc/db.php sử dụng PDO để kết nối, giúp ngăn chặn tấn công SQL Injection.

Mật khẩu nên được mã hóa bằng password_hash() khi đưa lên môi trường thực tế (Hiện tại demo đang dùng text thường để dễ kiểm tra).

Cần cấu hình upload_max_filesize trong php.ini lên mức cao (ví dụ 50M) để upload được tài liệu khóa học.