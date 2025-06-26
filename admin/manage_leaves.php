<?php 
session_start(); 
require_once "../config/database.php"; 

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") { 
    header("Location: ../auth/login.php"); 
    exit(); 
} 

$status_filter = isset($_GET['status']) ? $_GET['status'] : "";

$query = "SELECT l.leave_id, e.name AS employee_name, l.start_date, l.end_date, l.leave_type, l.status, l.reason, l.approved_by 
          FROM leaves l 
          JOIN employees e ON l.employee_id = e.employee_id"; 

if ($status_filter) { 
    $query .= " WHERE l.status = ?"; 
}

$query .= " ORDER BY l.start_date DESC"; 
$stmt = $pdo->prepare($query); 

if ($status_filter) { 
    $stmt->execute([$status_filter]); 
} else { 
    $stmt->execute(); 
}

$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý nghỉ phép</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    <div class="container mt-4">
        <h2 class="text-center">Quản lý nghỉ phép</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Cập nhật thành công!</div>
        <?php endif; ?>

        <form method="GET" class="mb-3">
            <label for="status">Lọc theo trạng thái:</label> 
            <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả</option>
                <option value="Chờ duyệt" <?= $status_filter == "Chờ duyệt" ? "selected" : "" ?>>Chờ duyệt</option>
                <option value="Đã duyệt" <?= $status_filter == "Đã duyệt" ? "selected" : "" ?>>Đã duyệt</option>
                <option value="Bị từ chối" <?= $status_filter == "Bị từ chối" ? "selected" : "" ?>>Bị từ chối</option>
            </select>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nhân viên</th>
                    <th>Loại nghỉ</th>
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Lý do</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaves as $leave): ?>
                    <tr>
                        <td><?= htmlspecialchars($leave['employee_name']) ?></td>
                        <td><?= htmlspecialchars($leave['leave_type']) ?></td>
                        <td><?= $leave['start_date'] ?></td>
                        <td><?= $leave['end_date'] ?></td>
                        <td><?= htmlspecialchars($leave['reason'] ?? '') ?></td>
                        <td><strong><?= $leave['status'] ?></strong></td>
                        <td>
                            <?php if ($leave['status'] === 'Chờ duyệt'): ?>
                                <form method="POST" action="process_leave.php" style="display:inline;">
                                    <input type="hidden" name="leave_id" value="<?= $leave['leave_id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success btn-sm">Duyệt</button>
                                </form>
                                <form method="POST" action="process_leave.php" style="display:inline;">
                                    <input type="hidden" name="leave_id" value="<?= $leave['leave_id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger btn-sm">Từ chối</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include "../includes/footer.php"; ?>
</body>
</html>
