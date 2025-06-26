<?php 
session_start();
require_once "../config/database.php";

// Kiểm tra quyền Admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Lấy danh sách nhân viên
$stmt = $pdo->query("SELECT employee_id, name FROM employees ORDER BY name"); 
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tháng & năm từ GET, nếu không có thì lấy tháng & năm hiện tại
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Lấy danh sách thưởng/phạt
$query = "SELECT bp.id, e.name, bp.amount, bp.type, bp.reason, bp.month, bp.year 
          FROM bonuses_penalties bp 
          JOIN employees e ON bp.employee_id = e.employee_id 
          WHERE bp.month = ? AND bp.year = ? 
          ORDER BY e.name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute([$month, $year]);
$bonuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Thêm thưởng/phạt
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_bonus"])) {
    $employee_id = $_POST['employee_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $type = $_POST['type'] ?? null;
    $reason = $_POST['reason'] ?? null;
    $month = $_POST['month'] ?? date('m');
    $year = $_POST['year'] ?? date('Y');

    if ($employee_id && $amount && $type && $month && $year) {
        $stmt = $pdo->prepare("INSERT INTO bonuses_penalties (employee_id, amount, type, reason, month, year) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$employee_id, $amount, $type, $reason, $month, $year]);
        header("Location: managa_bonuses.php?month=$month&year=$year");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Vui lòng nhập đầy đủ thông tin!</div>";
    }
}

// Xóa thưởng/phạt
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM bonuses_penalties WHERE id = ?")->execute([$id]);
    header("Location: managa_bonuses.php?month=$month&year=$year");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý Thưởng/Phạt</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <div class="container mt-4">
        <h2 class="text-center">Quản lý Thưởng/Phạt</h2>

        <!-- Bộ lọc -->
        <form method="GET" class="row mb-3">
            <div class="col-md-4">
                <label for="month">Tháng:</label>
                <select name="month" class="form-control">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $month) ? 'selected' : ''; ?>>Tháng <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="year">Năm:</label>
                <input type="number" name="year" class="form-control" value="<?= $year ?>" min="2000" max="2100">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Lọc</button>
            </div>
        </form>

        <!-- Bảng dữ liệu -->
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Nhân viên</th>
                    <th>Loại</th>
                    <th>Số tiền</th>
                    <th>Lý do</th>
                    <th>Tháng</th>
                    <th>Năm</th>
                    <th>Quản lý</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bonuses)): ?>
                    <?php foreach ($bonuses as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= $row['type'] ?></td>
                            <td><?= number_format($row['amount'], 0, ',', '.') ?> VND</td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                            <td><?= $row['month'] ?></td>
                            <td><?= $row['year'] ?></td>
                            <td>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa mục này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-danger">Không có dữ liệu</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Form thêm mới -->
        <h4 class="mt-4">Thêm Thưởng/Phạt</h4>
        <form method="POST" class="row">
            <div class="col-md-3">
                <label>Nhân viên:</label>
                <select name="employee_id" class="form-control">
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['employee_id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Loại:</label>
                <select name="type" class="form-control">
                    <option value="Thưởng">Thưởng</option>
                    <option value="Phạt">Phạt</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Số tiền:</label>
                <input type="number" name="amount" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Lý do:</label>
                <input type="text" name="reason" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Tháng:</label>
                <select name="month" class="form-control" required>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $month) ? 'selected' : ''; ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Năm:</label>
                <input type="number" name="year" class="form-control" value="<?= $year ?>" min="2000" max="2100" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="add_bonus" class="btn btn-success">Thêm</button>
            </div>
        </form>
    </div>

    <?php include "../includes/footer.php"; ?>
</body>
</html>
