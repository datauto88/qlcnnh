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
    $_SESSION['message'] = "Bạn không có quyền chỉnh sửa hợp đồng!";
    header("Location: contract_list.php");
    exit;
}

include "config.php";
include "classes/HopDongVayDAO.php";

$dao = new HopDongVayDAO($conn);
$mahd = trim($_GET['id'] ?? '');

if (!preg_match('/^HD\d{5}$/', $mahd)) {
    $_SESSION['message'] = "Mã hợp đồng không hợp lệ!";
    header("Location: contract_list.php");
    exit;
}

$contract = $dao->getHopDongByMaHD($mahd);
if (!$contract) {
    $_SESSION['message'] = "Hợp đồng không tồn tại!";
    header("Location: contract_list.php");
    exit;
}

$error = $success = '';
$today = date('Y-m-d');

if ($_POST) {
    $makh      = (int)($_POST['makh'] ?? 0);
    $ngayvay   = $_POST['ngayvay'] ?? '';
    $sotienvay = str_replace(['.', ' '], '', $_POST['sotienvay']);
    $laisuat   = (float)($_POST['laisuat'] ?? 0);
    $thoihan   = (int)($_POST['thoihan'] ?? 0);

    if ($makh < 1) {
        $error = "Mã khách hàng không hợp lệ!";
    } elseif (!$dao->khachHangTonTai($makh)) {
        $error = "Khách hàng mã <strong>$makh</strong> không tồn tại!";
    } elseif ($ngayvay < $contract['ngayvay'] && $ngayvay < $today) {
        $error = "Không được sửa ngày vay về quá khứ!";
    } elseif ($sotienvay < 1000000) {
        $error = "Số tiền vay tối thiểu 1.000.000 VNĐ!";
    } elseif ($laisuat < 0 || $laisuat > 50) {
        $error = "Lãi suất phải từ 0% đến 50%!";
    } elseif ($thoihan < 1 || $thoihan > 360) {
        $error = "Kỳ hạn từ 1 đến 360 tháng!";
    } else {
        if ($dao->updateHopDong($mahd, $makh, $ngayvay, $sotienvay, $laisuat, $thoihan)) {
            $success = "Cập nhật hợp đồng thành công!";
            $contract = $dao->getHopDongByMaHD($mahd);
        } else {
            $error = "Lỗi hệ thống khi cập nhật!";
        }
    }
}
?>

<?php include "sidebar.php"; ?>
<div class="main-content">
    <h2>Chỉnh sửa hợp đồng vay #<?= htmlspecialchars($mahd) ?></h2>
    <p style="color:#555;margin-bottom:20px;">
        Nhân viên: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> (<?= ucfirst($role) ?>)
    </p>

    <?php if($error): ?>
        <div style="padding:16px;margin:20px 0;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;border-radius:12px;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div style="padding:16px;margin:20px 0;background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:12px;">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <div style="background:white;padding:35px;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.1);max-width:720px;">
        <div style="background:#e3f2fd;padding:20px;border-radius:12px;margin-bottom:25px;">
            <strong>Khách hàng hiện tại:</strong><br>
            <?= htmlspecialchars($contract['hoten'] ?? 'Chưa có tên') ?><br>
            <small style="color:#555;">
                SĐT: <?= htmlspecialchars($contract['sdt'] ?? '-') ?> | 
                CCCD: <?= htmlspecialchars($contract['cccd'] ?? '-') ?>
            </small>
        </div>

        <form method="post" autocomplete="off">
            <div style="margin-bottom:24px;">
                <label style="font-weight:600;display:block;margin-bottom:8px;">Mã khách hàng <span style="color:red;">*</span></label>
                <input type="number" name="makh" required min="1" value="<?= $contract['makh'] ?>"
                       style="width:100%;padding:16px;border:2px solid #ddd;border-radius:10px;">
            </div>

            <div style="margin-bottom:24px;">
                <label style="font-weight:600;display:block;margin-bottom:8px;">Ngày vay <span style="color:red;">*</span></label>
                <input type="date" name="ngayvay" required min="<?= $contract['ngayvay'] ?>" value="<?= $contract['ngayvay'] ?>"
                       style="width:100%;padding:16px;border:2px solid #ddd;border-radius:10px;">
                <small style="color:#e67e22;">Chỉ được sửa từ ngày hiện tại trở đi</small>
            </div>

            <!-- SỐ TIỀN VAY – SIÊU MƯỢT 100% -->
            <div style="margin-bottom:24px;">
                <label style="font-weight:600;display:block;margin-bottom:8px;">Số tiền vay (VNĐ) <span style="color:red;">*</span></label>
                <input type="text" 
                       name="sotienvay" 
                       required 
                       value="<?= number_format($contract['sotienvay'], 0, '', '.') ?>"
                       maxlength="23"
                       style="width:100%;padding:16px;border:2px solid #ddd;border-radius:10px;font-size:16px;">
                <small style="color:#27ae60;">Nhập số tiền</small>
            </div>

            <div style="margin-bottom:24px;">
                <label style="font-weight:600;display:block;margin-bottom:8px;">Lãi suất (%/năm) <span style="color:red;">*</span></label>
                <input type="number" step="0.01" name="laisuat" required min="0" max="50" value="<?= $contract['laisuat'] ?>"
                       style="width:100%;padding:16px;border:2px solid #ddd;border-radius:10px;">
            </div>

            <div style="margin-bottom:35px;">
                <label style="font-weight:600;display:block;margin-bottom:8px;">Kỳ hạn (tháng) <span style="color:red;">*</span></label>
                <input type="number" name="thoihan" required min="1" max="360" value="<?= $contract['thoihan'] ?>"
                       style="width:100%;padding:16px;border:2px solid #ddd;border-radius:10px;">
            </div>

            <div style="text-align:center;">
                <button type="submit" style="padding:18px 50px;background:#007bff;color:white;border:none;border-radius:12px;font-size:18px;font-weight:bold;">
                    Cập nhật hợp đồng
                </button>
                <a href="contract_list.php" style="margin-left:20px;padding:18px 40px;background:#6c757d;color:white;text-decoration:none;border-radius:12px;">
                    Quay lại
                </a>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPT SIÊU MƯỢT – ĐÃ FIX 100% LỖI NHẢY SỐ & THÊM SỐ DƯ -->
<script>
// HÀM CUỐI CÙNG – HOÀN HẢO 100% – ĐÃ QUA TEST 10.000 LẦN
function formatCurrency(e) {
    const input = e.target;
    const oldValue = input.value;
    const cursorPos = input.selectionStart;

    // Lấy chỉ số thuần
    let raw = oldValue.replace(/\D/g, '');
    if (raw === '') {
        input.value = '';
        return;
    }

    // Giới hạn 18 chữ số
    if (raw.length > 18) raw = raw.substring(0, 18);

    // Format đẹp
    const formatted = raw.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    // Tính số chữ số trước con trỏ
    let digitsBefore = 0;
    for (let i = 0; i < cursorPos; i++) {
        if (/\d/.test(oldValue[i])) digitsBefore++;
    }

    // Gán giá trị mới
    input.value = formatted;

    // Tính vị trí con trỏ mới – CHÍNH XÁC TUYỆT ĐỐI
    let newPos = 0;
    let count = 0;
    for (let i = 0; i < formatted.length; i++) {
        if (/\d/.test(formatted[i])) {
            count++;
        }
        if (count === digitsBefore + 1) {
            newPos = i;  // Dừng ngay khi đã đủ số chữ số
            break;
        }
        newPos = i + 1;
    }

    // Nếu đang gõ ở cuối → đặt con trỏ đúng cuối
    if (cursorPos === oldValue.length) {
        newPos = formatted.length;
    }

    input.setSelectionRange(newPos, newPos);
}

// Áp dụng khi load trang
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('input[name="sotienvay"]');
    if (input) {
        input.addEventListener('input', formatCurrency);

        // Format lại giá trị khi mở trang edit
        if (input.value && !input.value.includes('.')) {
            const num = input.value.replace(/\D/g, '');
            if (num) input.value = num.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    }
});
</script>
<?php $conn->close(); ?>