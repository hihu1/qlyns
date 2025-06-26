<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

$error = "";
$success = "";

// Xóa thưởng/phạt
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM bonuses_penalties WHERE id = ?");
    $stmt->execute([$delete_id]);
    $success = "Xóa thành công.";
}

// Thêm thưởng/phạt nếu gửi form
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['edit_id'])) {
    $employee_id = $_POST["employee_id"] ?? "";
    $month = $_POST["month"] ?? "";
    $year = $_POST["year"] ?? "";
    $type = $_POST["type"] ?? "";
    $amount = $_POST["amount"] ?? 0;
    $reason = $_POST["reason"] ?? "";

    if (!$employee_id || !$month || !$year || !$type || !$amount || !$reason) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO bonuses_penalties (employee_id, month, year, type, amount, reason) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$employee_id, $month, $year, $type, $amount, $reason]);
            $success = "Thêm mới thành công!";
        } catch (PDOException $e) {
            $error = "Lỗi: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Lấy danh sách thưởng/phạt
$stmt = $pdo->query("SELECT b.*, e.name FROM bonuses_penalties b JOIN employees e ON b.employee_id = e.employee_id ORDER BY year DESC, month DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách nhân viên
$employees = $pdo->query("SELECT employee_id, name FROM employees")->fetchAll(PDO::FETCH_ASSOC);
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
    <h2 class="text-center">Quản lý Thưởng / Phạt</h2>

    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

    <form method="POST" class="card p-4 mb-4">
        <div class="row">
            <div class="col-md-4">
                <label>Nhân viên</label>
                <select name="employee_id" class="form-select" required>
                    <option value="">-- Chọn nhân viên --</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['employee_id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Tháng</label>
                <input type="number" name="month" min="1" max="12" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>Năm</label>
                <input type="number" name="year" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>Loại</label>
                <select name="type" class="form-select" required>
                    <option value="Thưởng">Thưởng</option>
                    <option value="Phạt">Phạt</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Số tiền</label>
                <input type="number" name="amount" class="form-control" required>
            </div>
        </div>
        <div class="mt-3">
            <label>Lý do</label>
            <textarea name="reason" class="form-control" rows="2" required></textarea>
        </div>
        <button class="btn btn-primary mt-3" type="submit">Thêm</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nhân viên</th>
                <th>Tháng</th>
                <th>Năm</th>
                <th>Loại</th>
                <th>Số tiền</th>
                <th>Lý do</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= $row['month'] ?></td>
                    <td><?= $row['year'] ?></td>
                    <td><?= $row['type'] ?></td>
                    <td><?= number_format($row['amount']) ?> VNĐ</td>
                    <td><?= htmlspecialchars($row['reason']) ?></td>
                    <td>
                        <a href="edit_bonus.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                        <a href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?')" class="btn btn-sm btn-danger">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include "../includes/footer.php"; ?>
</body>
</html>