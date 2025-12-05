--
-- Cơ sở dữ liệu: `course_db`
-- (Bạn có thể tạo CSDL này trước qua phpMyAdmin)
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đổ dữ liệu mẫu cho bảng `admins`
-- Mật khẩu mặc định là: admin123
--

INSERT INTO `admins` (`id`, `username`, `password_hash`) VALUES
(1, 'admin', '$2y$10$E.qJ4P6b.Qp1i/1a3f.C5uG01M0zS8.J3R.YY.l/dPFU3b9Yg0j8m');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_description` text NOT NULL,
  `full_description` longtext NOT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duration` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đổ dữ liệu mẫu cho bảng `courses`
--

INSERT INTO `courses` (`id`, `name`, `short_description`, `full_description`, `fee`, `duration`) VALUES
(1, 'Lập trình PHP và MySQL cơ bản', 'Khóa học nhập môn về lập trình web phía server với PHP và CSDL MySQL.', 'Đây là mô tả đầy đủ cho khóa học PHP. Bạn sẽ học về biến, mảng, hàm, vòng lặp, kết nối CSDL PDO, prepared statements, và xây dựng một dự án nhỏ.', 500000.00, '20 giờ'),
(2, 'Thiết kế Web Responsive với HTML5 & CSS3', 'Học cách xây dựng giao diện web đẹp, chuyên nghiệp và responsive.', 'Nội dung khóa học bao gồm: <br><ul><li>HTML5 semantics</li><li>CSS Flexbox</li><li>CSS Grid</li><li>Media Queries</li><li>Xây dựng landing page từ A-Z.</li></ul>', 350000.00, '15 giờ');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `student_email` varchar(255) NOT NULL,
  `student_phone` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Đang xử lý','Đã xác nhận','Đã hủy') NOT NULL DEFAULT 'Đang xử lý',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đổ dữ liệu mẫu cho bảng `registrations`
--

INSERT INTO `registrations` (`id`, `course_id`, `student_name`, `student_email`, `student_phone`, `notes`, `status`, `registered_at`) VALUES
(1, 1, 'Nguyễn Văn An', 'an.nguyen@example.com', '0987654321', 'Tôi muốn học cấp tốc.', 'Đang xử lý', NOW());

--
-- Chỉ mục cho các bảng
--

--
-- AUTO_INCREMENT cho bảng `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Các ràng buộc cho các bảng
--

--
-- Ràng buộc cho bảng `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;