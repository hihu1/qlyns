<?php
session_start();
require_once "../config/database.php";

// Kiểm tra đăng nhập và quyền truy cập
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Nhân viên") {
    header("Location: ../auth/login.php");
    exit();
}

$employee_id = $_SESSION["employee_id"] ?? null;
if (!$employee_id) {
    die("Lỗi: Không tìm thấy ID nhân viên trong session.");
}

// Lấy tháng & năm từ GET, mặc định là tháng & năm hiện tại
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

try {
    // Truy vấn lương của nhân viên đăng nhập
    $query = "SELECT p.month, p.year, p.total_hours, p.base_salary,
                     COALESCE(SUM(CASE WHEN bp.type = 'Thưởng' THEN bp.amount ELSE 0 END), 0) AS total_bonus,
                     COALESCE(SUM(CASE WHEN bp.type = 'Phạt' THEN bp.amount ELSE 0 END), 0) AS total_penalty,
                     (p.base_salary * p.total_hours 
                     + COALESCE(SUM(CASE WHEN bp.type = 'Thưởng' THEN bp.amount ELSE 0 END), 0) 
                     - COALESCE(SUM(CASE WHEN bp.type = 'Phạt' THEN bp.amount ELSE 0 END), 0)) AS total_salary
              FROM payroll p
              LEFT JOIN bonuses_penalties bp 
              ON p.employee_id = bp.employee_id AND p.month = bp.month AND p.year = bp.year
              WHERE p.employee_id = ? AND p.month = ? AND p.year = ?
              GROUP BY p.month, p.year, p.total_hours, p.base_salary";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$employee_id, $month, $year]);
    $salary = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lương Nhân Viên</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <div class="container mt-4">
        <h2 class="text-center">Thông Tin Lương</h2>

        <!-- Form chọn tháng/năm -->
        <form method="GET" action="salary.php" class="mb-3">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label>Tháng:</label>
                    <select name="month" class="form-control">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($m == $month) ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Năm:</label>
                    <select name="year" class="form-control">
                        <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                            <option value="<?= $y ?>" <?= ($y == $year) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">Xem</button>
                </div>
            </div>
        </form>

        <!-- Bảng chi tiết lương -->
        <div class="card">
            <div class="card-header bg-info text-white">Chi Tiết Lương</div>
            <div class="card-body">
                <?php if ($salary): ?>
                    <table class="table table-bordered">
                        <tr>
                            <th>Tháng</th>
                            <td><?= htmlspecialchars($salary['month']) ?></td>
                        </tr>
                        <tr>
                            <th>Năm</th>
                            <td><?= htmlspecialchars($salary['year']) ?></td>
                        </tr>
                        <tr>
                            <th>Số Giờ Làm Việc</th>
                            <td><?= htmlspecialchars($salary['total_hours']) ?></td>
                        </tr>
                        <tr>
                            <th>Lương Cơ Bản</th>
                            <td><?= number_format($salary['base_salary'], 0, ',', '.') ?> VND</td>
                        </tr>
                        <tr>
                            <th>Thưởng</th>
                            <td><?= number_format($salary['total_bonus'], 0, ',', '.') ?> VND</td>
                        </tr>
                        <tr>
                            <th>Phạt</th>
                            <td><?= number_format($salary['total_penalty'], 0, ',', '.') ?> VND</td>
                        </tr>
                        <tr>
                            <th>Tổng Lương</th>
                            <td><strong><?= number_format($salary['total_salary'], 0, ',', '.') ?> VND</strong></td>
                        </tr>
                    </table>
                <?php else: ?>
                    <p class="text-danger text-center">Không có dữ liệu lương cho tháng <?= $month ?>/<?= $year ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>
</body>
</html>
