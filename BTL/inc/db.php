<?php
// 1. Thiết lập múi giờ cho PHP (Việt Nam)
date_default_timezone_set('Asia/Ho_Chi_Minh');

define('DB_HOST', 'localhost');
define('DB_NAME', 'course_db'); // Tên CSDL của bạn
define('DB_USER', 'root');      // User mặc định
define('DB_PASS', 'mat_khau_moi_123');          // Pass mặc định

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // 2. Thiết lập múi giờ cho MySQL (Đồng bộ với PHP)
    $pdo->exec("SET time_zone = '+07:00'");
    
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>