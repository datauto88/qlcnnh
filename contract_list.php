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

$allowed_roles = ['admin', 'kinhdoanh', 'ketoan', 'cskh'];
if (!in_array($role, $allowed_roles)) {
    $_SESSION['message'] = "Bạn không có quyền truy cập trang này!";
    header("Location: sidebar.php");
    exit;
}

include "config.php";
include "classes/HopDongVayDAO.php"; // <<<--- THÊM DÒNG NÀY

// Khởi tạo DAO
$hopDongDAO = new HopDongVayDAO($conn);

// Lấy từ khóa tìm kiếm
$keyword = trim($_GET['keyword'] ?? '');

// DÙNG DAO ĐỂ LẤY DỮ LIỆU → SIÊU SẠCH!
$result = $hopDongDAO->getAllHopDong($keyword);
?>

<?php include "sidebar.php"; ?>

<div class="main-content">
    <h2>Danh sách hợp đồng vay</h2>
    <p style="color:#555; margin-bottom:20px; font-size:15px;">
        Nhân viên: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> 
        (<?= ucfirst($role) ?>)
    </p>

    <?php if (isset($_SESSION['message'])): ?>
        <div style="padding:16px; margin:20px 0; background:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:12px; font-weight:500;">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <?php unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <div style="background:white; padding:20px; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.1); margin-bottom:25px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <form method="get" style="display:flex; gap:15px; flex:1; min-width:300px;">
            <input type="text" name="keyword" 
                   placeholder="Nhập mã HĐ (VD: HD00105) hoặc mã KH (VD: 105)" 
                   value="<?= htmlspecialchars($keyword) ?>" 
                   style="flex:1; padding:14px 18px; border:2px solid #ddd; border-radius:10px; font-size:16px;">
            <button type="submit" style="padding:14px 30px; background:#1e40af; color:white; border:none; border-radius:10px; font-weight:600; cursor:pointer;">
                Tìm kiếm
            </button>
            <?php if($keyword): ?>
                <a href="contract_list.php" style="padding:14px 25px; background:#6c757d; color:white; text-decoration:none; border-radius:10px;">
                    Xóa lọc
                </a>
            <?php endif; ?>
        </form>

        <a href="contract_add.php" style="padding:14px 30px; background:#28a745; color:white; text-decoration:none; border-radius:10px; font-weight:bold;">
            + Mở hợp đồng mới
        </a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div style="background:white; border-radius:14px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.12);">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="background:linear-gradient(135deg,#1e40af,#1e3a8a); color:white;">
                    <tr>
                        <th style="padding:16px; width:100px;">Mã HĐ</th>
                        <th style="padding:16px; text-align:left;">Khách hàng</th>
                        <th style="padding:16px;">SĐT</th>
                        <th style="padding:16px;">CCCD</th>
                        <th style="padding:16px;">Ngày vay</th>
                        <th style="padding:16px; text-align:right;">Số tiền vay</th>
                        <th style="padding:16px;">Lãi suất</th>
                        <th style="padding:16px;">Kỳ hạn</th>
                        <th style="padding:16px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr style="border-bottom:1px solid #eee; transition:0.3s;" 
                        onmouseover="this.style.background='#f8f9ff'" 
                        onmouseout="this.style.background='white'">
                        <td style="text-align:center; font-weight:bold; color:#1e40af; font-size:16px;">
                            <?= htmlspecialchars($row['mahd']) ?>
                        </td>
                        <td style="text-align:left; font-weight:500;">
                            <?= htmlspecialchars($row['hoten'] ?? 'Chưa có tên') ?>
                            <br><small style="color:#666;">(Mã KH: <?= $row['makh'] ?>)</small>
                        </td>
                        <td><?= htmlspecialchars($row['sdt'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['cccd'] ?? '-') ?></td>
                        <td><?= $row['ngayvay'] ? date('d/m/Y', strtotime($row['ngayvay'])) : '-' ?></td>
                        <td style="text-align:right; color:#d4380d; font-weight:bold;">
                            <?= number_format($row['sotienvay'], 0, ',', '.') ?> ₫
                        </td>
                        <td style="text-align:center;"><?= number_format($row['laisuat'], 2) ?>%</td>
                        <td style="text-align:center;"><?= $row['thoihan'] ?> tháng</td>
                        <td style="text-align:center;">
                            <a href="contract_edit.php?id=<?= $row['mahd'] ?>" style="color:#007bff; text-decoration:none;">Sửa</a>
                            <span style="color:#aaa;"> | </span>
                            <a href="contract_delete.php?id=<?= $row['mahd'] ?>" 
                               onclick="return confirm('Xóa hợp đồng <?= $row['mahd'] ?> của <?= htmlspecialchars(addslashes($row['hoten'] ?? '')) ?>?');"
                               style="color:#dc3545; text-decoration:none;">Xóa</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align:center; padding:90px; background:white; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.1); color:#777; font-size:18px;">
            <?= $keyword ? "Không tìm thấy hợp đồng nào cho '<strong>" . htmlspecialchars($keyword) . "</strong>'" : "Chưa có hợp đồng vay nào." ?>
        </div>
    <?php endif; ?>

    <div style="margin-top:30px; padding:20px; background:white; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.1); font-size:16px; color:#444;">
        Tổng cộng: <strong style="color:#1e40af; font-size:21px;"><?= $result->num_rows ?></strong> hợp đồng vay
        <?php if($keyword): ?>
            <span style="margin-left:25px; color:#666;">
                (đã lọc theo: <em style="color:#d4380d;"><?= htmlspecialchars($keyword) ?></em>)
            </span>
        <?php endif; ?>
    </div>
</div>

<?php
$result->free();
$conn->close();
?>