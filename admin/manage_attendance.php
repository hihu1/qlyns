<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

$search = $_GET['search'] ?? '';
$date_filter = $_GET['date'] ?? '';
$month = $_GET['month'] ?? '';
$year = $_GET['year'] ?? '';

try {
    // Truy vấn dữ liệu chấm công
    $query = "SELECT a.attendance_id, e.name, a.date, a.check_in, a.check_out, a.work_hours, a.status
              FROM attendance a
              JOIN employees e ON a.employee_id = e.employee_id";
    $conditions = [];
    $params = [];

    if (!empty($search)) {
        $conditions[] = "e.name LIKE ?";
        $params[] = "%$search%";
    }

    if (!empty($date_filter)) {
        $conditions[] = "a.date = ?";
        $params[] = $date_filter;
    }

    if (!empty($month) && !empty($year)) {
        $conditions[] = "MONTH(a.date) = ? AND YEAR(a.date) = ?";
        $params[] = $month;
        $params[] = $year;
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY a.date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $attendances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Truy vấn thống kê
    $statsQuery = "SELECT e.name, COUNT(a.date) AS total_days, 
                          ROUND(AVG(a.work_hours), 2) AS avg_hours
                   FROM attendance a
                   JOIN employees e ON a.employee_id = e.employee_id
                   WHERE a.work_hours > 0
                   GROUP BY e.name";
    $statsStmt = $pdo->prepare($statsQuery);
    $statsStmt->execute();
    $statistics = $statsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý chấm công</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
<?php include "../includes/header.php"; ?>
<div class="container mt-4">
    <h2 class="text-center">Quản lý chấm công</h2>
    <form method="GET" class="row mb-3">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="month" class="form-control" placeholder="Tháng" min="1" max="12" value="<?php echo htmlspecialchars($month); ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="year" class="form-control" placeholder="Năm" min="2000" value="<?php echo htmlspecialchars($year); ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary">Lọc</button>
            <a href="manage_attendance.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nhân viên</th>
                <th>Ngày</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Số giờ làm</th>
                <th>Trạng thái</th>
                <th>Quản lý</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attendances as $index => $row): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo $row['check_in'] ?? '-'; ?></td>
                    <td><?php echo $row['check_out'] ?? '-'; ?></td>
                    <td><?php echo $row['work_hours'] ?? '0'; ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>
                        <a href="edit_attendance.php?id=<?php echo htmlspecialchars($row['attendance_id']); ?>" class="btn btn-warning btn-sm">Chỉnh sửa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3 class="mt-4">Thống kê chấm công</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nhân viên</th>
                <th>Tổng số ngày làm việc</th>
                <th>Số giờ làm trung bình mỗi ngày</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($statistics as $stat): ?>
                <tr>
                    <td><?php echo htmlspecialchars($stat['name']); ?></td>
                    <td><?php echo $stat['total_days']; ?></td>
                    <td><?php echo $stat['avg_hours']; ?> giờ</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include "../includes/footer.php"; ?>
</body>
</html>
