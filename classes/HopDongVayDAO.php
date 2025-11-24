<?php
// classes/HopDongVayDAO.php
class HopDongVayDAO {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    // Lấy danh sách hợp đồng (dùng ở list)
    public function getAllHopDong($keyword = '') {
        if ($keyword !== '') {
            if (is_numeric($keyword)) {
                $sql = "SELECT hd.mahd, hd.makh, kh.hoten, kh.sdt, kh.cccd, 
                               hd.ngayvay, hd.sotienvay, hd.laisuat, hd.thoihan 
                        FROM hopdongvay hd
                        LEFT JOIN khachhang kh ON hd.makh = kh.makh
                        WHERE hd.mahd = ? OR hd.makh = ?
                        ORDER BY hd.mahd DESC";
                $stmt = $this->conn->prepare($sql);
                $hdCode = 'HD' . str_pad($keyword, 5, '0', STR_PAD_LEFT);
                $stmt->bind_param("si", $hdCode, $keyword);
            } else {
                $sql = "SELECT hd.*, kh.hoten, kh.sdt, kh.cccd 
                        FROM hopdongvay hd
                        LEFT JOIN khachhang kh ON hd.makh = kh.makh
                        WHERE hd.mahd = ?
                        ORDER BY hd.mahd DESC";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("s", $keyword);
            }
        } else {
            $sql = "SELECT hd.mahd, hd.makh, kh.hoten, kh.sdt, kh.cccd, 
                           hd.ngayvay, hd.sotienvay, hd.laisuat, hd.thoihan 
                    FROM hopdongvay hd
                    LEFT JOIN khachhang kh ON hd.makh = kh.makh
                    ORDER BY hd.mahd DESC";
            $stmt = $this->conn->prepare($sql);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    // Sinh mã HD mới
    public function generateMaHD() {
        $sql = "SELECT MAX(CAST(SUBSTRING(mahd, 3) AS UNSIGNED)) AS max_id FROM hopdongvay WHERE mahd LIKE 'HD%'";
        $res = $this->conn->query($sql);
        $row = $res->fetch_assoc();
        $next = ($row['max_id'] ?? 0) + 1;
        return 'HD' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    // Kiểm tra khách hàng tồn tại
    public function khachHangTonTai($makh) {
        $sql = "SELECT makh FROM khachhang WHERE makh = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $makh);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Lấy thông tin hợp đồng + khách hàng theo mahd (dùng ở edit)
    public function getHopDongByMaHD($mahd) {
        $sql = "SELECT hd.*, kh.hoten, kh.sdt, kh.cccd 
                FROM hopdongvay hd 
                JOIN khachhang kh ON hd.makh = kh.makh 
                WHERE hd.mahd = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $mahd);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    // Thêm hợp đồng mới
    public function insertHopDong($mahd, $makh, $ngayvay, $sotienvay, $laisuat, $thoihan) {
        $sql = "INSERT INTO hopdongvay (mahd, makh, ngayvay, sotienvay, laisuat, thoihan) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sisdii", $mahd, $makh, $ngayvay, $sotienvay, $laisuat, $thoihan);
        return $stmt->execute();
    }

    // CẬP NHẬT hợp đồng
    public function updateHopDong($mahd, $makh, $ngayvay, $sotienvay, $laisuat, $thoihan) {
        $sql = "UPDATE hopdongvay 
                SET makh = ?, ngayvay = ?, sotienvay = ?, laisuat = ?, thoihan = ? 
                WHERE mahd = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isdids", $makh, $ngayvay, $sotienvay, $laisuat, $thoihan, $mahd);
        return $stmt->execute();
    }

    // Xóa hợp đồng
    public function deleteHopDong($mahd) {
        $sql = "DELETE FROM hopdongvay WHERE mahd = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $mahd);
        return $stmt->execute();
    }
}
?>