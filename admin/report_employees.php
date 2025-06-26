<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Thống kê nhân sự
$query_total = "SELECT COUNT(*) AS total FROM employees";
$query_active = "SELECT COUNT(*) AS active FROM employees WHERE status = 'Đang làm việc'";
$query_inactive = "SELECT COUNT(*) AS inactive FROM employees WHERE status = 'Đã nghỉ'";

$total = $pdo->query($query_total)->fetch(PDO::FETCH_ASSOC)['total'];
$active = $pdo->query($query_active)->fetch(PDO::FETCH_ASSOC)['active'];
$inactive = $pdo->query($query_inactive)->fetch(PDO::FETCH_ASSOC)['inactive'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo Nhân sự</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <div class="container mt-4">
        <h2 class="text-center">Báo cáo Nhân sự</h2>
        <ul class="list-group">
            <li class="list-group-item">Tổng số nhân viên: <strong><?= $total ?></strong></li>
            <li class="list-group-item">Nhân viên đang làm việc: <strong><?= $active ?></strong></li>
            <li class="list-group-item">Nhân viên đã nghỉ: <strong><?= $inactive ?></strong></li>
        </ul>
        <a href="export_employees.php" class="btn btn-primary mt-3">Xuất Excel</a>
    </div>

    <?php include "../includes/footer.php"; ?>
</body>
</html>
