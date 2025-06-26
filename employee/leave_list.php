<?php 
session_start(); 
require_once "../config/database.php"; 

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Nhân viên") { 
    header("Location: ../auth/login.php"); 
    exit(); 
} 

$employee_id = $_SESSION["employee_id"] ?? null; 
if (!$employee_id) { 
    die("Lỗi: Không tìm thấy ID nhân viên trong session."); 
}

$query = "SELECT leave_id, start_date, end_date, leave_type, reason, status, approved_by 
          FROM leaves WHERE employee_id = ? ORDER BY start_date DESC"; 
$stmt = $pdo->prepare($query); 
$stmt->execute([$employee_id]); 
$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Danh sách đơn nghỉ phép</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    <div class="container mt-4">
        <h2 class="text-center">Danh sách đơn nghỉ phép của tôi</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Loại nghỉ</th>
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Lý do</th>
                    <th>Trạng thái</th>
                    <th>Người duyệt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaves as $leave): ?>
                    <tr>
                        <td><?= htmlspecialchars($leave['leave_type']) ?></td>
                        <td><?= $leave['start_date'] ?></td>
                        <td><?= $leave['end_date'] ?></td>
                        <td><?= htmlspecialchars($leave['reason']) ?></td>
                        <td><strong><?= $leave['status'] ?></strong></td>
                        <td><?= htmlspecialchars($leave['approved_by'] ?? "Chưa có") ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="request_leave.php" class="btn btn-primary">Gửi đơn nghỉ phép mới</a>
    </div>
    <?php include "../includes/footer.php"; ?>
</body>
</html>
