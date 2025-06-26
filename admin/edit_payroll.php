<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

$payroll_id = $_GET['id'] ?? null;
if (!$payroll_id) {
    die("Thiếu ID bảng lương.");
}

try {
    $query = "SELECT * FROM payroll WHERE payroll_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$payroll_id]);
    $payroll = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payroll) {
        die("Không tìm thấy dữ liệu lương.");
    }
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

// Cập nhật bảng lương
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $total_hours = $_POST["total_hours"];
    $base_salary = $_POST["base_salary"];
    $bonus = $_POST["bonus"];
    $penalty = $_POST["penalty"];
    $total_salary = ($total_hours * $base_salary) + $bonus - $penalty;

    try {
        $query = "UPDATE payroll SET total_hours = ?, base_salary = ?, bonus = ?, penalty = ?, total_salary = ?, updated_at = NOW()
                  WHERE payroll_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$total_hours, $base_salary, $bonus, $penalty, $total_salary, $payroll_id]);

        header("Location: manage_payroll.php?month={$payroll['month']}&year={$payroll['year']}");
        exit();
    } catch (PDOException $e) {
        die("Lỗi cập nhật: " . $e->getMessage());
    }
}
?>
