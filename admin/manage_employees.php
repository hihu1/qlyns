<?php

session_start();

require_once "../config/database.php";

// Check Admin rights

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {

header("Location: ../auth/login.php");

exit();

}

// Handle search and filter

$search = isset($_GET["search"]) ? trim($_GET["search"]): "";

$department = isset($_GET["department"]) ? trim($_GET["department"]) : "";

$query = "SELECT * FROM employees WHERE 1";

$params = [];

if ($search) {

$query .= "AND name LIKE ?";

$params[] = "%$search%";

}

if ($department) {

$query .= "AND department = ?";

$params[] = $department;

}

$stmt = $pdo->prepare($query);

$stmt->execute($params);

$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Lấy danh sách phòng ban

$dept_stmt = $pdo->query("SELECT DISTINCT department FROM employees"); $departments = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);

?>

<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Quản lý nhân viên</title>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

</head>

<body>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">

<h2 class="text-center">Danh sách nhân viên</h2>

<div class="row mb-3">

<div class="col-md-6">

<form method="GET" class="d-flex">

<input type="text" name="search" class="form-control me-2"

placeholder="Tìm kiếm theo tên" value="<?php echo htmlspecialchars($search); ?>">

<select name="department" class="form-control me-2">

<option value="">Tất cả phòng ban</option>

<?php foreach ($departments as $dept): ?>

<option value="<?php echo $dept; ?>" <?php if

($dept == $department) echo "selected"; ?>><?php echo $dept; ?></option>

<?php endforeach; ?>

</select>

<button type="submit" class="btn btn-primary">Lọc</button>

</form>

</div>

<div class="col-md-6 text-end">

<a href="create_user.php" class="btn btn-success">+ Thêm nhân

viên</a>

</div>

</div>

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>ID</th>

<th>Họ và tên</th>

<th>Điện thoại</th>

<th>Email</th>

<th>Chức vụ</th>

<th>Phòng ban</th>

<th>Hành động</th>

</tr>

</thead>

<tbody>

<?php foreach ($employees as $employee): ?>

<tr>

<td><?php echo $employee["employee_id"]; ?></td>

<td><?php echo htmlspecialchars($employee["name"]);

?></td>

<td><?php echo htmlspecialchars($employee["phone"]);

?></td>

<td><?php echo htmlspecialchars($employee ["email"]);

?></td>

<td><?php echo htmlspecialchars($employee ["position"]);

?></td>

<td><?php echo

htmlspecialchars($employee ["department"]); ?></td>

<td>

<a href="edit_employee.php?id=<?php echo

$employee["employee_id"]; ?>" class="btn btn-warning btn-sm">Sửa</a>

<a href="delete_employee.php?id=<?php echo

$employee["employee_id"]; ?>" class="btn btn-danger btn-sm" onclick="return

confirm('Bạn có chắc chân muốn xóa?');">Xóa</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php include "../includes/footer.php"; ?>

</body>

</html>

