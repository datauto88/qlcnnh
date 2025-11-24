<?php
session_start();
ob_start();

// === KIỂM TRA ĐĂNG NHẬP & PHÂN QUYỀN ===
if (!isset($_SESSION['username']) || empty($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = trim(strtolower($_SESSION['role']));
if ($role === "khachhang") {
    header("Location: khachhang_home.php");
    exit;
}

// Chỉ admin + kinhdoanh + ketoan mới được xóa hợp đồng
$allowed_roles = ['admin', 'kinhdoanh', 'ketoan'];
if (!in_array($role, $allowed_roles)) {
    $_SESSION['message'] = "Bạn không có quyền xóa hợp đồng vay!";
    header("Location: contract_list.php");
    exit;
}

include "config.php";
include "classes/HopDongVayDAO.php"; // DÙNG DAO

// Khởi tạo DAO
$dao = new HopDongVayDAO($conn);

// Lấy mã hợp đồng từ URL (giờ mahd là HDxxxxx nên dùng string)
$mahd = trim($_GET['id'] ?? '');

if ($mahd === '' || !preg_match('/^HD\d{5}$/', $mahd)) {
    $_SESSION['message'] = "Mã hợp đồng không hợp lệ!";
    header("Location: contract_list.php");
    exit;
}

// === DÙNG DAO ĐỂ XÓA ===
if ($dao->deleteHopDong($mahd)) {
    $_SESSION['message'] = "Xóa hợp đồng <strong>$mahd</strong> thành công!";
} else {
    $_SESSION['message'] = "Hợp đồng <strong>$mahd</strong> không tồn tại hoặc đã bị xóa!";
}

$conn->close();
header("Location: contract_list.php");
exit;
?>