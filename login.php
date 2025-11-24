<?php
session_start();
ob_start();
include "config.php";

$error = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM taikhoan WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user["password"])) {
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"]     = $user["role"];
                $_SESSION["makh"]     = $user["makh"] ?? null;

                $role = trim(strtolower($user["role"]));
                if ($role === "khachhang") {
                    header("Location: khachhang_home.php");
                } else {
                    header("Location: sidebar.php");
                }
                exit;
            }
        }
        $error = "Sai tên đăng nhập hoặc mật khẩu!";
    } else {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Ngân hàng ĐĐHH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #1a3e60ff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .login-wrapper {
            display: flex;
            width: 950px;
            height: 580px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.18);
        }
        /* === BÊN TRÁI: ẢNH NHÂN VIÊN NGÂN HÀNG LÀM VIỆC === */
        .image-side {
            flex: 1.1;
            position: relative;
            background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.5)),
                        url('https://images.unsplash.com/photo-1577962917302-cd874c4e31d2?q=80&w=2232&auto=format&fit=crop') center/cover no-repeat;
        }
        .image-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(30,64,175,0.8), rgba(59,130,246,0.7));
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 50px;
            color: white;
        }
        .image-overlay h1 {
            font-size: 38px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .image-overlay p {
            font-size: 18px;
            opacity: 0.95;
            line-height: 1.6;
        }
        .logo-circle {
            width: 90px;
            height: 90px;
            background: white;
            color: #1e40af;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
            position: absolute;
            top: 40px;
            left: 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        /* === BÊN PHẢI: FORM ĐĂNG NHẬP === */
        .form-side {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }
        .form-side h2 {
            font-size: 29px;
            color: #1e3a8a;
            text-align: center;
            margin-bottom: 8px;
        }
        .form-side p {
            text-align: center;
            color: #64748b;
            margin-bottom: 35px;
            font-size: 15px;
        }
        .input-group {
            margin-bottom: 22px;
            position: relative;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        .input-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        .input-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
        }
        button {
            width: 100%;
            padding: 15px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        button:hover {
            background: #1e3a8a;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30,64,175,0.3);
        }
        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 14px;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }
        .register-link {
            text-align: center;
            margin-top: 28px;
            font-size: 15px;
            color: #64748b;
        }
        .register-link a {
            color: #3b82f6;
            font-weight: 600;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .login-wrapper { width: 90%; height: auto; flex-direction: column; }
            .image-side { height: 300px; }
            .form-side { padding: 50px 40px; }
        }
        @media (max-width: 576px) {
            .login-wrapper { border-radius: 16px; }
            .form-side { padding: 40px 25px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Bên trái: Hình nhân viên ngân hàng làm việc -->
    <div class="image-side">
        <div class="logo-circle">ĐĐHH</div>
        <div class="image-overlay">
            <h1>Ngân hàng ĐĐHH</h1>
            <p>Hệ thống quản lý hiện đại – Dịch vụ chuyên nghiệp<br>Đồng hành cùng sự phát triển của bạn</p>
        </div>
    </div>

    <!-- Bên phải: Form đăng nhập -->
    <div class="form-side">
        <h2>Đăng nhập hệ thống</h2>
        <p>Chào mừng bạn trở lại!</p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="input-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required autofocus>
            </div>

            <div class="input-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit">Đăng nhập ngay</button>
        </form>

        <div class="register-link">
            Bạn là khách hàng? 
            <a href="register.php">Đăng ký tài khoản ngay</a>
        </div>
    </div>
</div>

</body>
</html>