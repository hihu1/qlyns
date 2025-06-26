<?php
session_start();
require_once "../config/database.php";

// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra quyền Admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Kiểm tra kết nối PDO
if (!isset($pdo)) {
    die("Lỗi kết nối CSDL. Hãy kiểm tra lại file config/database.php.");
}

// Lấy tháng & năm từ GET, nếu không có thì lấy tháng & năm hiện tại
$month = isset($_GET['month']) ? (int) $_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int) $_GET['year'] : date('Y');

try {
    // Truy vấn dữ liệu từ bảng payroll và tính tổng thưởng/phạt từ bảng bonuses_penalties
    $query = "SELECT p.payroll_id, e.name, p.month, p.year, p.total_hours, p.base_salary,
                     COALESCE(SUM(CASE WHEN bp.type = 'Thưởng' THEN bp.amount ELSE 0 END), 0) AS total_bonus,
                     COALESCE(SUM(CASE WHEN bp.type = 'Phạt' THEN bp.amount ELSE 0 END), 0) AS total_penalty,
                     (p.base_salary * p.total_hours +
                      COALESCE(SUM(CASE WHEN bp.type = 'Thưởng' THEN bp.amount ELSE 0 END), 0) -
                      COALESCE(SUM(CASE WHEN bp.type = 'Phạt' THEN bp.amount ELSE 0 END), 0)) AS total_salary
              FROM payroll p
              JOIN employees e ON p.employee_id = e.employee_id
              LEFT JOIN bonuses_penalties bp ON p.employee_id = bp.employee_id AND p.month = bp.month AND p.year = bp.year
              WHERE p.month = ? AND p.year = ?
              GROUP BY p.payroll_id, e.name, p.month, p.year, p.total_hours, p.base_salary
              ORDER BY e.name ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$month, $year]);
    $payrolls = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý Lương</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    <div class="container mt-4">
        <h2 class="text-center">Quản lý Lương</h2>
        <!-- Bộ lọc tháng & năm -->
        <form method="GET" class="row mb-3">
            <div class="col-md-4">
                <label for="month">Tháng:</label>
                <select name="month" class="form-control">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($i == $month) ? 'selected' : ''; ?>>Tháng <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="year">Năm:</label>
<input type="number" name="year" class="form-control" value="<?php echo $year; ?>" min="2000" max="2100">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Xem</button>
            </div>
        </form>

        <!-- Bảng dữ liệu -->
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Nhân viên</th>
                    <th>Tháng</th>
                    <th>Năm</th>
                    <th>Tổng giờ làm</th>
                    <th>Lương cơ bản</th>
                    <th>Thưởng</th>
                    <th>Phạt</th>
                    <th>Lương thực nhận</th>
                    <th>Quản lý</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payrolls)): ?>
                    <?php foreach ($payrolls as $index => $row): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo $row['month']; ?></td>
                            <td><?php echo $row['year']; ?></td>
                            <td><?php echo $row['total_hours']; ?></td>
                            <td><?php echo number_format($row['base_salary'], 0, ',', '.'); ?> đ</td>
                            <td><?php echo number_format($row['total_bonus'], 0, ',', '.'); ?> đ</td>
                            <td><?php echo number_format($row['total_penalty'], 0, ',', '.'); ?> đ</td>
                            <td><?php echo number_format($row['total_salary'], 0, ',', '.'); ?> đ</td>
                            <td>
                                <a href="edit_payroll.php?id=<?php echo htmlspecialchars($row['payroll_id']); ?>" class="btn btn-warning btn-sm">Chỉnh sửa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center text-danger">Không có dữ liệu lương cho tháng <?php echo $month; ?> năm <?php echo $year; ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php include "../includes/footer.php"; ?>
</body>
</html>