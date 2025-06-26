<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php"; // Dùng đúng file kết nối

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $position = trim($_POST["position"]);
    $department = trim($_POST["department"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]); // Xử lý mật khẩu
    $role = $_POST["role"];
    $base_salary = isset($_POST["base_salary"]) ? floatval($_POST["base_salary"]) : 0;

    // Kiểm tra lương cơ bản phải lớn hơn 0
    if ($base_salary <= 0) {
        $error = "Lương cơ bản phải lớn hơn 0";
    } else {
        try {
            $pdo->beginTransaction();

            // Kiểm tra trùng tên đăng nhập
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.");
            }

            // Thêm nhân viên vào bảng employees (GIỮ base_salary)
            $stmt = $pdo->prepare("INSERT INTO employees (name, phone, email, position, department, base_salary, created_by)
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $phone, $email, $position, $department, $base_salary, $_SESSION["user_id"]]);

            // Lấy employee_id vừa tạo
            $employee_id = $pdo->lastInsertId();

            // Mã hóa mật khẩu trước khi lưu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Thêm tài khoản vào bảng users
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, status, employee_id)
                                    VALUES (?, ?, ?, 'Chờ kích hoạt', ?)");
            $stmt->execute([$username, $hashed_password, $role, $employee_id]);

            // Lưu lịch sử lương vào bảng salary
            $stmt = $pdo->prepare("INSERT INTO salary (employee_id, base_salary, effective_date, updated_by)
                                    VALUES (?, ?, CURRENT_DATE, ?)");
            $stmt->execute([$employee_id, $base_salary, $_SESSION["user_id"]]);

            $pdo->commit();
            $success = "Tạo tài khoản thành công!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tạo tài khoản</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
    <h2 class="text-center"> Tạo tài khoản nhân viên</h2>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-6">
                <label>Họ và Tên</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>Điện thoại</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>Vị trí</label>
                <input type="text" name="position" class="form-control" required>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6">
                <label>Phòng ban</label>
                <input type="text" name="department" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" required>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6">
                <label>Mật khẩu</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>Quyền</label>
                <select name="role" class="form-control" required>
                    <option value="Nhân viên">Nhân viên</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6">
                <label>Lương cơ bản (VNĐ)</label>
                <input type="number" name="base_salary" class="form-control" required min="0">
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Tạo tài khoản</button>
    </form>
</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
