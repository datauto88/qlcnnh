<?php
session_start();
include "config.php";

// 🔐 Kiểm tra xem đã đăng nhập chưa
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "khachhang") {
    header("Location: login.php");
    exit();
}

// 🧍‍♂️ Lấy mã khách hàng từ session
$makh = $_SESSION["makh"] ?? null;
if (!$makh) {
    echo "Không tìm thấy thông tin khách hàng!";
    exit();
}

// 📊 Lấy thông tin khách hàng từ bảng khachhang
$stmt = $conn->prepare("SELECT * FROM khachhang WHERE makh = ?");
$stmt->bind_param("i", $makh);
$stmt->execute();
$result = $stmt->get_result();
$khachhang = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang khách hàng</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0; padding: 0;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            height: 100vh;
            background-color: #f4f6f8;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background-color: #1e3a8a;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar h2 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 25px;
        }
        .nav-links a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 8px;
            transition: background 0.3s;
        }
        .nav-links a:hover {
            background-color: #2563eb;
        }
        .logout-btn {
            text-align: center;
            background-color: #dc2626;
            padding: 10px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-weight: bold;
        }
        .logout-btn:hover {
            background-color: #b91c1c;
        }

        /* Content area */
        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        .header {
            font-size: 22px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 20px;
        }
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            max-width: 800px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            width: 200px;
            color: #2563eb;
        }
        tr:hover {
            background-color: #f0f4ff;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <h2>💼 KHÁCH HÀNG</h2>
            <div class="nav-links">
                <a href="khachhang_home.php">🏠 Trang chính</a>
                <a href="khachhang_profile.php">👤 Thông tin cá nhân</a>
                <a href="khachhang_tietkiem.php">💰 Sổ tiết kiệm</a>
                <a href="khachhang_vay.php">💼 Hợp đồng vay</a>
                <a href="khachhang_thuchi.php">📄 Phiếu thu chi</a>
                <a href="khachhang_hotro.php">💬 Liên hệ hỗ trợ</a>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">🚪 Đăng xuất</a>
    </div>

    <div class="content">
        <div class="header">
            Xin chào, <?php echo htmlspecialchars($khachhang["hoten"]); ?> 👋
        </div>

        <div class="info-card">
            <h3>📋 Thông tin khách hàng</h3>
            <table>
                <tr><th>Mã khách hàng</th><td><?php echo htmlspecialchars($khachhang["makh"]); ?></td></tr>
                <tr><th>Họ và tên</th><td><?php echo htmlspecialchars($khachhang["hoten"]); ?></td></tr>
                <tr><th>CCCD</th><td><?php echo htmlspecialchars($khachhang["cccd"]); ?></td></tr>
                <tr><th>Ngày sinh</th><td><?php echo htmlspecialchars($khachhang["ngaysinh"]); ?></td></tr>
                <tr><th>Giới tính</th><td><?php echo htmlspecialchars($khachhang["gioitinh"]); ?></td></tr>
                <tr><th>Địa chỉ</th><td><?php echo htmlspecialchars($khachhang["diachi"]); ?></td></tr>
                <tr><th>Số điện thoại</th><td><?php echo htmlspecialchars($khachhang["sdt"]); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($khachhang["email"]); ?></td></tr>
            </table>
        </div>
    </div>

</body>
</html>
