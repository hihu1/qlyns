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

// Xử lý cập nhật trạng thái lương
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (isset($_POST["calculate_salary"])) {
            $updateQuery = "UPDATE payroll SET status = 'Đã tính' WHERE status = 'Chưa tính' AND month = ? AND year = ?";
            $stmtUpdate = $pdo->prepare($updateQuery);
            $stmtUpdate->execute([$month, $year]);
            $message = "Lương đã được tính cho tháng $month/$year.";
        } elseif (isset($_POST["pay_salary"])) {
            $updateQuery = "UPDATE payroll SET status = 'Đã thanh toán' WHERE status = 'Đã tính' AND month = ? AND year = ?";
            $stmtUpdate = $pdo->prepare($updateQuery);
            $stmtUpdate->execute([$month, $year]);
            $message = "Lương đã được thanh toán cho tháng $month/$year.";
        }
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Truy vấn tổng quỹ lương (tính cả thưởng/phạt)
$query_total_salary = "SELECT SUM(p.total_salary + IFNULL(b.total_bonus, 0) - IFNULL(b.total_penalty, 0)) AS total_salary 
FROM payroll p 
LEFT JOIN (
    SELECT employee_id, 
        SUM(CASE WHEN type = 'Thưởng' THEN amount ELSE 0 END) AS total_bonus, 
        SUM(CASE WHEN type = 'Phạt' THEN amount ELSE 0 END) AS total_penalty
    FROM bonuses_penalties 
    WHERE month = ? AND year = ? 
    GROUP BY employee_id
) b ON p.employee_id = b.employee_id
WHERE p.status = 'Đã tính' AND p.month = ? AND p.year = ?";

$stmt = $pdo->prepare($query_total_salary);
$stmt->execute([$month, $year, $month, $year]);
$total_salary = $stmt->fetch(PDO::FETCH_ASSOC)['total_salary'] ?? 0;

// Truy vấn chi tiết lương nhân viên kèm thưởng/phạt
$query_salary_details = "SELECT e.employee_id, e.name, p.total_hours, p.base_salary, p.total_salary, p.status,
    IFNULL(b.total_bonus, 0) AS total_bonus, 
    IFNULL(b.total_penalty, 0) AS total_penalty, 
    (p.total_salary + IFNULL(b.total_bonus, 0) - IFNULL(b.total_penalty, 0)) AS final_salary
FROM payroll p
JOIN employees e ON p.employee_id = e.employee_id
LEFT JOIN (
    SELECT employee_id, 
        SUM(CASE WHEN type = 'Thưởng' THEN amount ELSE 0 END) AS total_bonus, 
        SUM(CASE WHEN type = 'Phạt' THEN amount ELSE 0 END) AS total_penalty
    FROM bonuses_penalties 
    WHERE month = ? AND year = ? 
    GROUP BY employee_id
) b ON p.employee_id = b.employee_id
WHERE p.month = ? AND p.year = ?";

$stmt = $pdo->prepare($query_salary_details);
$stmt->execute([$month, $year, $month, $year]);
$salary_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo Lương</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <div class="container mt-4">
        <h2 class="text-center">Báo cáo Lương</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

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

        <!-- Hiển thị tổng quỹ lương -->
        <ul class="list-group">
            <li class="list-group-item">
                <strong>Tổng quỹ lương tháng <?= $month ?>/<?= $year ?>:</strong> <?= number_format($total_salary, 0, ',', '.') ?> VND
            </li>
        </ul>

        <!-- Nút tính lương & thanh toán lương -->
        <form method="POST" class="mt-3">
            <button type="submit" name="calculate_salary" class="btn btn-warning">Tính lương</button>
            <button type="submit" name="pay_salary" class="btn btn-success">Thanh toán lương</button>
        </form>

        <!-- Bảng chi tiết lương nhân viên -->
        <h3 class="mt-4">Chi tiết lương nhân viên</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Mã NV</th>
                    <th>Họ và Tên</th>
                    <th>Số giờ làm</th>
                    <th>Lương cơ bản</th>
                    <th>Tổng thưởng</th>
                    <th>Tổng phạt</th>
                    <th>Lương thực nhận</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salary_details as $row): ?>
                <tr>
                    <td><?= $row['employee_id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= $row['total_hours'] ?> giờ</td>
                    <td><?= number_format($row['base_salary'], 0, ',', '.') ?> VND</td>
                    <td><?= number_format($row['total_bonus'], 0, ',', '.') ?> VND</td>
                    <td><?= number_format($row['total_penalty'], 0, ',', '.') ?> VND</td>
                    <td><strong><?= number_format($row['final_salary'], 0, ',', '.') ?> VND</strong></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="export.php?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-success mt-3">Xuất Excel</a>
    </div>

    <?php include "../includes/footer.php"; ?>
</body>
</html>
