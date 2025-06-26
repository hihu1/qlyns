<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Sửa dòng này
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];
        $_SESSION["employee_id"] = $user["employee_id"];

        // ✅ Điều hướng theo vai trò
        header("Location:" . ($user["role"] == "Admin" ? "../admin/dashboard.php" : "../employee/dashboard.php"));
        exit();
    } else {
        $error = "Sai tài khoản hoặc mật khẩu!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="card p-4 w-25">
        <h3 class="text-center">Đăng nhập</h3>
        <form method="post">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
        </form>
        <?php if (isset($error)) echo "<p class='text-danger text-center'>$error</p>"; ?>
    </div>
</body>
</html>
