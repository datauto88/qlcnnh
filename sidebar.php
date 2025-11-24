
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}
$role = trim(strtolower($_SESSION["role"] ?? ''));
if ($role === "khachhang") {
    header("Location: khachhang_home.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ngân Hàng ĐĐHH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background:#f8f9fa; }

        /* SIDEBAR CỐ ĐỊNH - CHUẨN 260px */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 260px;
            height: 100%;
            background: linear-gradient(180deg, #1e40af, #1e3a8a);
            color: white;
            padding: 20px 0;
            box-shadow: 4px 0 15px rgba(0,0,0,0.2);
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar h3 {
            text-align: center;
            padding: 20px 0;
            font-size: 24px;
            background: rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .user-info {
            text-align: center;
            padding: 15px;
            background: rgba(255,255,255,0.15);
            margin: 0 15px 25px;
            border-radius: 12px;
            font-size: 15px;
        }
        .sidebar ul { list-style: none; }
        .sidebar ul li { margin: 8px 15px; }
        .sidebar ul li a {
            display: block;
            padding: 14px 18px;
            color: #e0e7ff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background: #3b82f6;
            color: white;
            padding-left: 25px;
            box-shadow: 0 4px 15px rgba(59,130,246,0.4);
        }
        .sidebar ul li a i { margin-right: 12px; }

        /* NỘI DUNG CHÍNH - CHỈ 1 LỚP DUY NHẤT */
        .main-content {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        .logout {
            position: absolute;
            bottom: 20px;
            left: 15px;
            right: 15px;
        }
        .logout a {
            display: block;
            padding: 14px;
            background: #dc3545;
            color: white;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>Ngân Hàng ĐĐHH</h3>
    <div class="user-info">
        Chào, <strong><?= htmlspecialchars($_SESSION["username"]) ?></strong><br>
        <small><?= ucfirst($role) ?></small>
    </div>
    <ul>
        <li><a href="customer_list.php" <?= basename($_SERVER['PHP_SELF'])=='customer_list.php'?'class="active"':'' ?>>
            <i class="fas fa-users"></i> Danh sách khách hàng
        </a></li>
        <li><a href="contract_add.php" <?= basename($_SERVER['PHP_SELF'])=='contract_add.php'?'class="active"':'' ?>>
            <i class="fas fa-file-signature"></i> Mở hợp đồng mới
        </a></li>
        <li><a href="contract_list.php" <?= basename($_SERVER['PHP_SELF'])=='contract_list.php'?'class="active"':'' ?>>
            <i class="fas fa-folder-open"></i> Danh sách hợp đồng
        </a></li>
    </ul>
    <div class="logout">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
    </div>
</div>

<!-- NỘI DUNG CHÍNH BẮT ĐẦU TỪ ĐÂY -->
<div class="main-content">