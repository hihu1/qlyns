<?php session_start(); ?> 
<!DOCTYPE html> 
<html lang="vi"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <title>Hệ thống Quản lý Nhân sự</title> 
    <link rel="stylesheet" href="assets/css/bootstrap.min.css"> 
</head> 
<body> 
    <!-- Thanh điều hướng --> 
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark"> 
        <div class="container"> 
            <a class="navbar-brand" href="index.php">HRM System</a> 
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"> 
                <span class="navbar-toggler-icon"></span> 
            </button> 
            <div class="collapse navbar-collapse" id="navbarNav"> 
                <ul class="navbar-nav ms-auto"> 
                    <?php if (isset($_SESSION["user_id"])): ?> 
                        <?php if ($_SESSION["role"] == "Admin"): ?> 
                            <li class="nav-item"><a class="nav-link" href="admin/dashboard.php">Admin Dashboard</a></li> 
                            <li class="nav-item"><a class="nav-link" href="admin/create_user.php">Tạo tài khoản</a></li> 
                        <?php else: ?> 
                            <li class="nav-item"><a class="nav-link" href="employee/profile.php">Hồ sơ cá nhân</a></li>   
                            <li class="nav-item"><a class="nav-link" href="employee/change_password.php">Đổi mật khẩu</a></li> 
                        <?php endif; ?> 
                        <li class="nav-item"><a class="nav-link text-danger" href="auth/logout.php">Đăng xuất</a></li> 
                    <?php else: ?> 
                        <li class="nav-item"><a class="nav-link" href="auth/login.php">Đăng nhập</a></li> 
                    <?php endif; ?> 
                </ul> 
            </div> 
        </div> 
    </nav> 
    <!-- Nội dung chính --> 
    <div class="container mt-5"> 
        <div class="text-center"> 
            <h1>Chào mừng đến với Hệ thống Quản lý Nhân sự</h1> 
            <p class="lead">Quản lý nhân sự dễ dàng và hiệu quả</p> 
            <?php if (!isset($_SESSION["user_id"])): ?> 
                <a href="auth/login.php" class="btn btn-primary btn-lg">Đăng nhập ngay</a> 
            <?php else: ?> 
                <a href="<?= $_SESSION["role"] == 'Admin' ? 'admin/dashboard.php' : 'employee/dashboard.php' ?>" class="btn btn-success btn-lg">Vào Hệ thống</a> 
            <?php endif; ?> 
        </div> 
    </div> 
    <!-- Footer --> 
    <footer class="bg-dark text-white text-center p-3 mt-5"> 
        <p>&copy; 2025 Hệ thống Quản lý Nhân sự</p> 
    </footer> 
    
    <script src="assets/js/bootstrap.bundle.js"></script>
</body> 
</html>
