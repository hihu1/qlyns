<?php
$host = "127.0.0.1";  // Đảm bảo không có khoảng trắng thừa
$dbname = "myhrm";
$username = "root";  // Tên người dùng MySQL
$password = "";  // Mật khẩu (rỗng nếu chưa đặt mật khẩu cho root)

try {
    // Kết nối với cơ sở dữ liệu MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Thiết lập chế độ báo lỗi
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // In ra thông báo lỗi nếu kết nối thất bại
    die("Lỗi kết nối: " . $e->getMessage());
}
?>
