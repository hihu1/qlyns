<?php
session_start();
require_once "../config/database.php";

// Kiểm tra quyền Admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Xử lý thêm lương cơ bản mới
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['employee_id'], $_POST['base_salary'], $_POST['effective_date'])) {
    $employee_id = $_POST['employee_id'];
    $base_salary = $_POST['base_salary'];
    $effective_date = $_POST['effective_date'];

    try {
        $stmt = $pdo->prepare("INSERT INTO salary (employee_id, base_salary, effective_date) VALUES (?, ?, ?)");
        $stmt->execute([$employee_id, $base_salary, $effective_date]);
        $success_message = "Lương cơ bản mới đã được cập nhật!";
    } catch (PDOException $e) {
        $error_message = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách nhân viên
$employees = $pdo->query("SELECT employee_id, name FROM employees ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Lấy lương mới nhất của từng nhân viên
$query = "SELECT s.employee_id, e.name, s.base_salary, s.effective_date
          FROM salary s
          JOIN employees e ON s.employee_id = e.employee_id
          WHERE s.effective_date = (SELECT MAX(effective_date) FROM salary WHERE employee_id = s.employee_id)
          ORDER BY e.name ASC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$current_salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy toàn bộ lịch sử lương
$history_query = "SELECT s.salary_id, e.name, s.base_salary, s.effective_date, s.updated_at
                  FROM salary s
                  JOIN employees e ON s.employee_id = e.employee_id
                  ORDER BY e.name ASC, s.effective_date DESC";
$history_stmt = $pdo->prepare($history_query);
$history_stmt->execute();
$salary_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý Lương Cơ Bản</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <div class="container mt-4">
        <h2 class="text-center">Quản lý Lương Cơ Bản</h2>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"> <?= $success_message; ?> </div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"> <?= $error_message; ?> </div>
        <?php endif; ?>

        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label>Nhân viên:</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">Chọn nhân viên</option>
                        <?php foreach ($employees as $employee): ?>
<option value="<?= $employee['employee_id']; ?>">
                                <?= htmlspecialchars($employee['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Lương cơ bản:</label>
                    <input type="number" name="base_salary" class="form-control" step="0.01" required>
                </div>
                <div class="col-md-3">
                    <label>Ngày hiệu lực:</label>
                    <input type="date" name="effective_date" class="form-control" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </div>
        </form>

        <h4>Lương hiện tại của nhân viên</h4>
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Nhân viên</th>
                    <th>Lương cơ bản</th>
                    <th>Ngày hiệu lực</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($current_salaries as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= number_format($row['base_salary'], 0, ',', '.'); ?> đ</td>
                        <td><?= $row['effective_date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Lịch sử lương</h4>
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Nhân viên</th>
                    <th>Lương cơ bản</th>
                    <th>Ngày hiệu lực</th>
                    <th>Cập nhật lúc</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salary_history as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= number_format($row['base_salary'], 0, ',', '.'); ?> đ</td>
                        <td><?= $row['effective_date']; ?></td>
                        <td><?= $row['updated_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include "../includes/footer.php"; ?>
</body>
</html>