<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Xác định tháng/năm từ request hoặc mặc định là tháng/năm hiện tại
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Tổng số ngày làm việc
$query_workdays = "SELECT COUNT(DISTINCT date) AS workdays FROM attendance WHERE MONTH(date) = ? AND YEAR(date) = ?";
$stmt = $pdo->prepare($query_workdays);
$stmt->execute([$month, $year]);
$workdays = $stmt->fetch(PDO::FETCH_ASSOC)['workdays'];

// Nhân viên đi trễ
$query_late = "SELECT COUNT(*) AS late FROM attendance WHERE notes LIKE '%Check-in: Đi trễ%' AND MONTH(date) = ? AND YEAR(date) = ?";
$stmt = $pdo->prepare($query_late);
$stmt->execute([$month, $year]);
$late = $stmt->fetch(PDO::FETCH_ASSOC)['late'];

// Nhân viên về sớm
$query_early_leave = "SELECT COUNT(*) AS early_leave FROM attendance WHERE notes LIKE '%Check-out: Về sớm%' AND MONTH(date) = ? AND YEAR(date) = ?";
$stmt = $pdo->prepare($query_early_leave);
$stmt->execute([$month, $year]);
$early_leave = $stmt->fetch(PDO::FETCH_ASSOC)['early_leave'];

// Truy vấn xu hướng đi muộn/về sớm theo ngày trong tháng
$query_trend = "SELECT DATE(date) AS work_date, 
    SUM(CASE WHEN notes LIKE '%Check-in: Đi trễ%' THEN 1 ELSE 0 END) AS late_count, 
    SUM(CASE WHEN notes LIKE '%Check-out: Về sớm%' THEN 1 ELSE 0 END) AS early_leave_count 
    FROM attendance WHERE MONTH(date) = ? AND YEAR(date) = ? GROUP BY work_date ORDER BY work_date";

$stmt = $pdo->prepare($query_trend);
$stmt->execute([$month, $year]);
$trend_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Chuyển dữ liệu thành JSON để sử dụng trong JavaScript
$dates = [];
$late_counts = [];
$early_leave_counts = [];

foreach ($trend_data as $row) {
    $dates[] = $row['work_date'];
    $late_counts[] = $row['late_count'] ?? 0;
    $early_leave_counts[] = $row['early_leave_count'] ?? 0;
}

$dates_json = json_encode($dates);
$late_counts_json = json_encode($late_counts);
$early_leave_counts_json = json_encode($early_leave_counts);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo Chấm công</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <div class="container mt-4">
        <h2 class="text-center">Báo cáo Chấm công</h2>

        <!-- Form chọn tháng/năm -->
        <form method="GET" class="mb-3 row">
            <div class="col-md-4">
                <label>Chọn tháng:</label>
                <select name="month" class="form-control">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $month) ? "selected" : "" ?>>Tháng <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Chọn năm:</label>
                <select name="year" class="form-control">
                    <?php for ($y = 2020; $y <= date('Y'); $y++): ?>
                        <option value="<?= $y ?>" <?= ($y == $year) ? "selected" : "" ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4 mt-4">
                <button type="submit" class="btn btn-primary">Xem báo cáo</button>
            </div>
        </form>

        <!-- Thông tin tổng hợp -->
        <ul class="list-group">
            <li class="list-group-item">Tổng số ngày làm việc: <strong><?= $workdays ?></strong></li>
            <li class="list-group-item">Số lần đi trễ: <strong><?= $late ?></strong></li>
            <li class="list-group-item">Số lần về sớm: <strong><?= $early_leave ?></strong></li>
        </ul>

        <!-- Biểu đồ -->
        <div class="mt-4">
            <canvas id="trendChart"></canvas>
        </div>

        <!-- Xuất Excel -->
        <a href="export_attendance.php?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-primary mt-3">Xuất Excel</a>
    </div>

    <?php include "../includes/footer.php"; ?>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById('trendChart').getContext('2d');

        var dates = <?= $dates_json ?>;
        var lateCounts = <?= $late_counts_json ?>;
        var earlyLeaveCounts = <?= $early_leave_counts_json ?>;

        var trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Đi trễ',
                        data: lateCounts,
                        borderColor: 'red',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Về sớm',
                        data: earlyLeaveCounts,
                        borderColor: 'blue',
                        backgroundColor: 'rgba(54, 84, 235, 0.2)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Ngày'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Số lần đi trễ / về sớm'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    });
    </script>

</body>
</html>
