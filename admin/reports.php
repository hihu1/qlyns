<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê & Báo cáo</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <div class="container mt-4">
        <h2 class="text-center">Thống kê & Báo cáo</h2>
        <div class="list-group">
            <a href="report_employees.php" class="list-group-item list-group-item-action">Báo cáo Nhân sự</a>
            <a href="report_attendance.php" class="list-group-item list-group-item-action">Báo cáo Chấm công</a>
            <a href="report_salary.php" class="list-group-item list-group-item-action">Báo cáo Lương</a>
        </div>
    </div>
    <?php include "../includes/footer.php"; ?>
</body>
</html>
