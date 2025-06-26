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

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $start_date = $_POST["start_date"]; 
    $end_date = $_POST["end_date"]; 
    $leave_type = $_POST["leave_type"]; 
    $reason = $_POST["reason"]; 

    if (strtotime($end_date) < strtotime($start_date)) { 
        $message = "Ngày kết thúc không thể sớm hơn ngày bắt đầu."; 
    } else { 
        $query = "INSERT INTO leaves (employee_id, start_date, end_date, leave_type, reason, status) 
                  VALUES (?, ?, ?, ?, ?, 'Chờ duyệt')"; 
        $stmt = $pdo->prepare($query); 
        if ($stmt->execute([$employee_id, $start_date, $end_date, $leave_type, $reason])) { 
            header("Location: leave_list.php"); 
            exit(); 
        } else { 
            $message = "Có lỗi xảy ra!"; 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yêu cầu nghỉ phép</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    <div class="container mt-4">
        <h2 class="text-center">Gửi yêu cầu nghỉ phép</h2>
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="request_leave.php" method="post">
            <div class="mb-3">
                <label for="start_date" class="form-label">Ngày bắt đầu:</label> 
                <input type="date" class="form-control" name="start_date" required>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">Ngày kết thúc:</label> 
                <input type="date" class="form-control" name="end_date" required>
            </div>
            <div class="mb-3">
                <label for="leave_type" class="form-label">Loại nghỉ phép:</label> 
                <select class="form-control" name="leave_type" required>
                    <option value="Nghỉ ốm">Nghỉ ốm</option>
                    <option value="Nghỉ phép thường">Nghỉ phép thường</option>
                    <option value="Nghỉ phép năm">Nghỉ phép năm</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="reason" class="form-label">Lý do:</label>
                <textarea class="form-control" name="reason" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Gửi yêu cầu</button>
        </form>
        <div class="text-center mt-3">
            <a href="leave_list.php" class="btn btn-secondary">Xem danh sách đơn nghỉ phép</a>
        </div>
    </div>
    <?php include "../includes/footer.php"; ?>
</body>
</html>
