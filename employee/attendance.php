<?php 
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

// Lấy employee_id từ user_id
$stmt = $pdo->prepare("SELECT employee_id FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);
$employee_id = $employee["employee_id"] ?? null;

if (!$employee_id) {
    die("Không tìm thấy nhân viên.");
}

// Lấy dữ liệu chấm công hôm nay
$date = date("Y-m-d");
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
$stmt->execute([$employee_id, $date]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["check_in"]) && !$attendance) {
        // Check-in
        $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, check_in) VALUES (?, ?, NOW())");
        $stmt->execute([$employee_id, $date]);
        header("Location: attendance.php");
        exit();
    } elseif (isset($_POST["check_out"]) && $attendance && !$attendance["check_out"]) {
        // Check-out
        $stmt = $pdo->prepare("UPDATE attendance SET check_out = NOW() WHERE attendance_id = ?");
        $stmt->execute([$attendance["attendance_id"]]);
        header("Location: attendance.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chấm công</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
<?php include "../includes/header.php"; ?>
<div class="container mt-4">
    <h2 class="text-center">Chấm công</h2>
    <div class="card p-4 text-center">
        <p><strong>Ngày:</strong> <?php echo $date; ?></p>
        <p><strong>Giờ vào:</strong> <?php echo $attendance["check_in"] ?? "Chưa chấm"; ?></p>
        <p><strong>Giờ ra:</strong> <?php echo $attendance["check_out"] ?? "Chưa chấm"; ?></p>
        <form method="post">
            <?php if (!$attendance): ?>
                <button type="submit" name="check_in" class="btn btn-success">Check-in</button>
            <?php elseif (!$attendance["check_out"]): ?>
                <button type="submit" name="check_out" class="btn btn-danger">Check-out</button>
            <?php else: ?>
                <p class="text-success">Bạn đã chấm công đầy đủ hôm nay.</p>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
</body>
</html>
