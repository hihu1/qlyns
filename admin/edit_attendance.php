<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID không hợp lệ.");
}

$attendance_id = $_GET['id'];

// Lấy dữ liệu chấm công cần chỉnh sửa
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE attendance_id = ?");
$stmt->execute([$attendance_id]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attendance) {
    die("Không tìm thấy dữ liệu chấm công.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE attendance SET check_in = ?, check_out = ?, status = ? WHERE attendance_id = ?");
    $stmt->execute([$check_in, $check_out, $status, $attendance_id]);

    header("Location: manage_attendance.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chỉnh sửa chấm công</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
<?php include "../includes/header.php"; ?>
<div class="container mt-4">
    <h2 class="text-center">Chỉnh sửa chấm công</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Check-in:</label>
            <input type="time" name="check_in" class="form-control" value="<?php echo htmlspecialchars($attendance['check_in']); ?>">
        </div>
        <div class="mb-3">
            <label>Check-out:</label>
            <input type="time" name="check_out" class="form-control" value="<?php echo htmlspecialchars($attendance['check_out']); ?>">
        </div>
        <div class="mb-3">
            <label>Trạng thái:</label>
            <select name="status" class="form-control">
                <option value="Có mặt" <?php echo ($attendance['status'] == "Có mặt") ? "selected" : ""; ?>>Có mặt</option>
                <option value="Vắng mặt" <?php echo ($attendance['status'] == "Vắng mặt") ? "selected" : ""; ?>>Vắng mặt</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
    </form>
</div>
<?php include "../includes/footer.php"; ?>
</body>
</html>
