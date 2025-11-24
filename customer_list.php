<?php
session_start();
ob_start();

if (!isset($_SESSION['username']) || empty($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = trim(strtolower($_SESSION['role']));
if ($role === "khachhang") {
    header("Location: khachhang_home.php");
    exit;
}

$allowed_roles = ['admin', 'kinhdoanh', 'cskh', 'ketoan', 'tietkiem'];
if (!in_array($role, $allowed_roles)) {
    $_SESSION['message'] = "Bạn không có quyền truy cập danh sách khách hàng!";
    header("Location: sidebar.php");
    exit;
}

include "config.php";

$keyword = trim($_GET['keyword'] ?? '');

if ($keyword !== '') {
    // Chỉ tìm theo mã khách hàng – chính xác và nhanh nhất
    $sql = "SELECT * FROM khachhang WHERE makh = ? OR makh LIKE ?";
    $stmt = $conn->prepare($sql);
    $like = "%" . $keyword . "%";
    $stmt->bind_param("is", $keyword, $like);  // "i" = integer cho tìm chính xác
} else {
    $sql = "SELECT * FROM khachhang ORDER BY makh DESC";
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách khách hàng - Ngân hàng ABC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background:#f5f7fa; }

        .main-content {
            margin-left: 240px !important;
            padding: 25px 35px;
            min-height: 100vh;
            max-width: calc(100vw - 240px);
        }
        @media (max-width: 1400px) { .main-content { margin-left: 220px !important; } }
        @media (max-width: 1200px) { .main-content { margin-left: 200px !important; } }
        @media (max-width: 992px)  { .main-content { margin-left: 0 !important; padding: 20px; } }

        h2 { color:#1e40af; font-size:27px; font-weight:600; margin-bottom:6px; }
        .info { color:#555; font-size:15px; margin-bottom:22px; }
        .info strong { color:#1e40af; }

        .search-bar {
            background:#fff; padding:18px 25px; border-radius:14px;
            box-shadow:0 6px 20px rgba(0,0,0,0.09); margin-bottom:28px;
            display:flex; gap:15px; align-items:center; flex-wrap:wrap;
        }
        .search-bar input {
            flex:1; min-width:320px; padding:14px 18px; border:2px solid #e2e8f0;
            border-radius:10px; font-size:16px;
        }
        .search-bar input:focus { border-color:#1e40af; outline:none; box-shadow:0 0 0 4px rgba(30,64,175,0.15); }
        .search-bar button, .search-bar a {
            padding:14px 28px; border:none; border-radius:10px; font-weight:600; cursor:pointer; text-decoration:none;
        }
        .search-bar button { background:#1e40af; color:white; }
        .search-bar button:hover { background:#1e3a8a; transform:translateY(-2px); }
        .search-bar a { background:#6c757d; color:white; }

        table { width:100%; border-collapse:collapse; background:white; border-radius:14px; overflow:hidden;
            box-shadow:0 10px 30px rgba(0,0,0,0.12); }
        thead { background:linear-gradient(135deg,#1e40af,#1e3a8a); color:white; }
        th, td { padding:16px 14px; text-align:center; }
        th { font-weight:600; }
        tbody tr { border-bottom:1px solid #f0f0f0; transition:0.3s; }
        tbody tr:hover { background:#f0f7ff; transform:translateY(-2px); box-shadow:0 8px 25px rgba(30,64,175,0.15); }

        .contract-link {
            display:inline-block; padding:9px 16px; background:#e0f2fe; color:#1e40af;
            font-weight:bold; border-radius:8px; text-decoration:none; transition:0.3s;
        }
        .contract-link:hover { background:#b3e5fc; transform:translateY(-3px); }

        .empty { text-align:center; padding:90px 20px; color:#777; font-size:18px; }
        .summary { margin-top:30px; padding:20px; background:white; border-radius:14px;
            box-shadow:0 6px 20px rgba(0,0,0,0.09); font-size:16px; color:#444; }
        .summary strong { color:#1e40af; font-size:21px; }
        .alert { padding:16px; margin:20px 0; background:#d4edda; color:#155724;
            border-radius:10px; border:1px solid #c3e6cb; font-weight:500; }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
    <h2>Danh sách khách hàng</h2>
    <div class="info">
        Nhân viên: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> 
        (<?= ucfirst($role) ?>)
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert"><?= htmlspecialchars($_SESSION['message']) ?><?php unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <div class="search-bar">
        <form method="get" style="display:flex;gap:15px;flex:1;">
            <input type="text" name="keyword" placeholder="Tìm mã KH, tên, CCCD, SĐT, email..." value="<?= htmlspecialchars($keyword) ?>">
            <button type="submit">Tìm kiếm</button>
            <?php if($keyword): ?><a href="customer_list.php">Xóa lọc</a><?php endif; ?>
        </form>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Mã KH</th><th>Họ và tên</th><th>CCCD</th><th>SĐT</th><th>Ngày sinh</th>
                    <th>Giới tính</th><th>Địa chỉ</th><th>Email</th><th>Hợp đồng vay</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php
                    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM hopdongvay WHERE makh = ?");
                    $count_stmt->bind_param("i", $row['makh']);
                    $count_stmt->execute();
                    $count_stmt->bind_result($count);
                    $count_stmt->fetch();
                    $count_stmt->close();
                    ?>
                    <tr>
                        <td><strong style="color:#1e40af;">#<?= $row['makh'] ?></strong></td>
                        <td style="text-align:left;font-weight:500;"><?= htmlspecialchars($row['hoten']) ?></td>
                        <td><?= htmlspecialchars($row['cccd'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['sdt'] ?? '-') ?></td>
                        <td><?= !empty($row['ngaysinh']) && $row['ngaysinh'] !== '0000-00-00' ? date('d/m/Y', strtotime($row['ngaysinh'])) : '-' ?></td>
                        <td><?= ($row['gioitinh'] ?? '') === 'Nam' ? 'Nam' : (($row['gioitinh'] ?? '') === 'Nữ' ? 'Nữ' : '-') ?></td>
                        <td style="text-align:left;color:#555;"><?= htmlspecialchars($row['diachi'] ?? '-') ?></td>
                        <td style="color:#0066cc;"><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                        <td><a href="contract_list.php?keyword=<?= $row['makh'] ?>" class="contract-link"><?= $count ?> hợp đồng →</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty">
            <?= $keyword ? "Không tìm thấy khách hàng nào cho '<strong>" . htmlspecialchars($keyword) . "</strong>'" : "Chưa có khách hàng nào trong hệ thống." ?>
        </div>
    <?php endif; ?>

    <div class="summary">
        Tổng cộng: <strong><?= $result->num_rows ?></strong> khách hàng
        <?php if($keyword): ?>
            <span style="margin-left:25px;color:#666;">(đã lọc theo: <em style="color:#d4380d;"><?= htmlspecialchars($keyword) ?></em>)</span>
        <?php endif; ?>
    </div>
</div>

<?php $stmt->close(); $conn->close(); ?>
</body>
</html>