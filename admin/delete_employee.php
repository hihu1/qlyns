<?php

session_start();

require_once "../config/database.php";

// Kiểm tra nếu người dùng không phải Admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: /auth/login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: manage_employees.php");
    exit();
}

$employee_id = $_GET["id"];

try {
    $pdo->beginTransaction();

    // Xóa tài khoản liên kết trước
    $stmt = $pdo->prepare("DELETE FROM users WHERE employee_id = ?");
    $stmt->execute([$employee_id]);

    // Xóa nhân viên
    $stmt = $pdo->prepare("DELETE FROM employees WHERE employee_id = ?");
    $stmt->execute([$employee_id]);

    // Nếu không có lỗi, commit transaction
    $pdo->commit();
} catch (PDOException $e) {
    // Nếu có lỗi, rollback transaction
    $pdo->rollBack();
    // Bạn có thể ghi lại lỗi hoặc hiển thị thông báo nếu cần
    echo "Lỗi: " . $e->getMessage();
    exit();
}

header("Location: manage_employees.php");
exit();

?>
