<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: manage_employees.php");
    exit();
}

$employee_id = $_GET["id"];

// Lấy thông tin nhân viên
$stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->execute([$employee_id]);

$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    header("Location: manage_employees.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {  // Sửa = thành ===
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $position = trim($_POST["position"]);
    $department = trim($_POST["department"]);

    // Cập nhật thông tin nhân viên
    $stmt = $pdo->prepare("UPDATE employees SET name = ?, phone = ?, email = ?, position = ?, department = ? WHERE employee_id = ?");
    $stmt->execute([$name, $phone, $email, $position, $department, $employee_id]);

    header("Location: manage_employees.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chỉnh sửa nhân viên</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
    <h2 class="text-center">Chỉnh sửa thông tin nhân viên</h2>

    <form method="POST">
        <div class="mb-3">
            <label>Họ và Tên</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($employee["name"]); ?>" required>
        </div>

        <div class="mb-3">
            <label>Điện thoại</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($employee["phone"]); ?>" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($employee["email"]); ?>" required>
        </div>

        <div class="mb-3">
            <label>Chức vụ</label>
            <input type="text" name="position" class="form-control" value="<?php echo htmlspecialchars($employee["position"]); ?>" required>
        </div>

        <div class="mb-3">
            <label>Phòng ban</label>
            <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($employee["department"]); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
    </form>
</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
