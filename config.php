<?php
// ===============================
// File: config.php
// Mục đích: Kết nối MySQL trong XAMPP
// ===============================

$servername = "localhost";
$username = "root"; // mặc định của XAMPP
$password = ""; // để trống nếu bạn chưa đặt mật khẩu
$dbname = "quanly_nganhang";

// Kết nối tới MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("❌ Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập tiếng Việt
$conn->set_charset("utf8mb4");
?>
