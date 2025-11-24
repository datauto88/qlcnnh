<?php
session_start();
include "config.php";

$error = "";
$data = []; // Giữ lại dữ liệu khi có lỗi

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Lấy và làm sạch dữ liệu
    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'password' => trim($_POST['password'] ?? ''),
        'confirm'  => trim($_POST['confirm'] ?? ''),
        'hoten'    => trim($_POST['hoten'] ?? ''),
        'ngaysinh' => trim($_POST['ngaysinh'] ?? ''),
        'gioitinh' => trim($_POST['gioitinh'] ?? ''),
        'diachi'   => trim($_POST['diachi'] ?? ''),
        'sdt'      => trim($_POST['sdt'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'cccd'     => trim($_POST['cccd'] ?? '')
    ];

    // ================== VALIDATE CƠ BẢN ==================
    if (in_array('', $data, true)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } elseif (strlen($data['password']) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự!";
    } elseif ($data['password'] !== $data['confirm']) {
        $error = "Mật khẩu xác nhận không khớp!";
    } elseif (!preg_match("/^0[0-9]{9}$/", $data['sdt'])) {
        $error = "Số điện thoại phải bắt đầu bằng 0 và có đúng 10 số!";
    } elseif (!preg_match("/^[0-9]{12}$/", $data['cccd'])) {
        $error = "CCCD phải gồm đúng 12 chữ số!";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } elseif (!empty($data['ngaysinh'])) {
        $birth = new DateTime($data['ngaysinh']);
        $today = new DateTime();
        if ($today->diff($birth)->y < 18) {
            $error = "Bạn phải đủ 18 tuổi trở lên!";
        }
    }

    // ================== KIỂM TRA TRÙNG (QUAN TRỌNG NHẤT) ==================
    if (empty($error)) {
        // 1. Kiểm tra USERNAME trùng
        $stmt = $conn->prepare("SELECT username FROM taikhoan WHERE username = ?");
        $stmt->bind_param("s", $data['username']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Tên đăng nhập đã được sử dụng!";
        }
        $stmt->close();
    }

    if (empty($error)) {
        // 2. KIỂM TRA CCCD TRÙNG (BẮT BUỘC – 1 NGƯỜI CHỈ CÓ 1 CCCD)
        $stmt = $conn->prepare("SELECT makh, hoten FROM khachhang WHERE cccd = ?");
        $stmt->bind_param("s", $data['cccd']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($old_makh, $old_name);
            $stmt->fetch();
            $error = "CCCD này đã thuộc về khách hàng: <strong>$old_name</strong> (Mã KH: $old_makh)<br>→ Không thể đăng ký trùng!";
        }
        $stmt->close();
    }

    if (empty($error)) {
        // 3. KIỂM TRA SỐ ĐIỆN THOẠI TRÙNG (rất nên có)
        $stmt = $conn->prepare("SELECT makh, hoten FROM khachhang WHERE sdt = ?");
        $stmt->bind_param("s", $data['sdt']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($old_makh, $old_name);
            $stmt->fetch();
            $error = "Số điện thoại này đã được dùng bởi khách hàng: <strong>$old_name</strong> (Mã KH: $old_makh)";
        }
        $stmt->close();
    }

    // ================== ĐĂNG KÝ THÀNH CÔNG ==================
    if (empty($error)) {
        try {
            $conn->begin_transaction();

            // Thêm khách hàng
            $sqlKH = "INSERT INTO khachhang (hoten, ngaysinh, gioitinh, diachi, sdt, email, cccd)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtKH = $conn->prepare($sqlKH);
            $stmtKH->bind_param("sssssss",
                $data['hoten'], $data['ngaysinh'], $data['gioitinh'],
                $data['diachi'], $data['sdt'], $data['email'], $data['cccd']
            );
            $stmtKH->execute();
            $makh = $stmtKH->insert_id;
            $stmtKH->close();

            // Thêm tài khoản
            $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
            $role = "khachhang";
            $sqlTK = "INSERT INTO taikhoan (username, password, role, makh) VALUES (?, ?, ?, ?)";
            $stmtTK = $conn->prepare($sqlTK);
            $stmtTK->bind_param("sssi", $data['username'], $hashed, $role, $makh);
            $stmtTK->execute();
            $stmtTK->close();

            $conn->commit();

            echo "<script>
                alert('Đăng ký thành công! Chào mừng bạn đến với Ngân hàng ABC.');
                window.location='login.php';
            </script>";
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Lỗi hệ thống, vui lòng thử lại sau!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng ký tài khoản khách hàng - Ngân hàng ABC</title>
<style>
    body{font-family:Arial,sans-serif;background:#f3f4f6;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;}
    .register-box{background:#fff;padding:40px 35px;border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.15);width:100%;max-width:500px;}
    h2{text-align:center;color:#1e3a8a;margin-bottom:25px;font-size:26px;font-weight:600;}
    label{display:block;margin:16px 0 7px;font-weight:600;color:#333;}
    input, select{width:100%;padding:12px;border:1.5px solid #ddd;border-radius:8px;font-size:15px;transition:0.3s;}
    input:focus, select:focus{border-color:#2563eb;outline:none;box-shadow:0 0 8px rgba(37,99,235,0.2);}
    button{width:100%;padding:14px;margin-top:25px;background:#1e40af;color:#fff;border:none;border-radius:8px;font-size:17px;cursor:pointer;font-weight:bold;transition:0.3s;}
    button:hover{background:#1e3a8a;transform:translateY(-2px);}
    .error{color:#d4380d;background:#ffe6e6;padding:14px;border-radius:8px;margin:15px 0;text-align:center;font-weight:bold;border:1px solid #fecaca;}
    .link{text-align:center;margin-top:25px;font-size:15px;}
    .link a{color:#1e40af;font-weight:600;text-decoration:none;}
    .link a:hover{text-decoration:underline;}
</style>
</head>
<body>
<div class="register-box">
    <h2>Đăng ký tài khoản khách hàng</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= nl2br(htmlspecialchars($error)) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <label>Họ và tên</label>
        <input type="text" name="hoten" value="<?= htmlspecialchars($data['hoten'] ?? '') ?>" required>

        <label>Ngày sinh</label>
        <input type="date" name="ngaysinh" value="<?= htmlspecialchars($data['ngaysinh'] ?? '') ?>" required>

        <label>Giới tính</label>
        <select name="gioitinh" required>
            <option value="">-- Chọn giới tính --</option>
            <option value="Nam" <?= ($data['gioitinh'] ?? '') === 'Nam' ? 'selected' : '' ?>>Nam</option>
            <option value="Nữ" <?= ($data['gioitinh'] ?? '') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
        </select>

        <label>Địa chỉ</label>
        <input type="text" name="diachi" value="<?= htmlspecialchars($data['diachi'] ?? '') ?>" required>

        <label>Số điện thoại</label>
        <input type="text" name="sdt" maxlength="10" placeholder="0912345678" 
               value="<?= htmlspecialchars($data['sdt'] ?? '') ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>

        <label>CCCD (12 số, không trùng)</label>
        <input type="text" name="cccd" maxlength="12" placeholder="012345678901" 
               value="<?= htmlspecialchars($data['cccd'] ?? '') ?>" required>

        <label>Tên đăng nhập</label>
        <input type="text" name="username" value="<?= htmlspecialchars($data['username'] ?? '') ?>" required>

        <label>Mật khẩu (tối thiểu 6 ký tự)</label>
        <input type="password" name="password" minlength="6" required>

        <label>Xác nhận mật khẩu</label>
        <input type="password" name="confirm" minlength="6" required>

        <button type="submit">Đăng ký ngay</button>
    </form>

    <div class="link">
        <p>Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a></p>
    </div>
</div>
</body>
</html>