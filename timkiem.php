<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm hợp đồng vay</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 50px auto;
            width: 850px;
            background: #f8f9fa;
        }
        h2 {
            text-align: center;
            color: #003366;
            text-transform: uppercase;
            margin-bottom: 25px;
        }
        form {
            background: white;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        label {
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;
            margin: 6px 0 12px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            background-color: #0074D9;
            color: white;
            border: none;
            padding: 10px;
            font-size: 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #005fa3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #003366;
            color: white;
        }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #e1f5fe; }
        .btn-edit, .btn-delete {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
        }
        .btn-edit {
            background-color: #28a745;
        }
        .btn-edit:hover {
            background-color: #1e7e34;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #b02a37;
        }
    </style>
</head>
<body>

<h2>TÌM KIẾM HỢP ĐỒNG VAY</h2>

<form method="GET">
    <label>Từ khóa tìm kiếm:</label>
    <input type="text" name="keyword" placeholder="Nhập mã hợp đồng, mã KH hoặc ngày vay...">
    <button type="submit">Tìm kiếm</button>
</form>

<!-- Dưới đây là mẫu hiển thị minh họa -->
<table>
    <tr>
        <th>Mã hợp đồng</th>
        <th>Mã KH</th>
        <th>Ngày vay</th>
        <th>Số tiền vay (VNĐ)</th>
        <th>Lãi suất (%)</th>
        <th>Thời hạn (tháng)</th>
        <th>Thao tác</th>
    </tr>
    <tr>
        <td>1</td>
        <td>KH001</td>
        <td>2024-10-15</td>
        <td>100,000,000</td>
        <td>8.50</td>
        <td>12</td>
        <td>
            <button class="btn-edit">Sửa</button>
            <button class="btn-delete">Xóa</button>
        </td>
    </tr>
</table>

</body>
</html>
