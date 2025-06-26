<?php
if (session_status() == PHP_SESSION_NONE) {
session_start();
}
?>
<nav class="navbar navbar-expand-1g navbar-dark bg-dark">
<div class="container">
<a class="navbar-brand" href="../index.php">HRM System</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav ms-auto">
<?php if (isset($_SESSION["user_id"])): ?>
<?php if ($_SESSION["role"] == "Admin"): ?>
<li class="nav-item"><a class="nav-link"
href="../admin/dashboard.php">Admin Dashboard</a></li>
<11 class="nav-item"><a class="nav-link"
href="../admin/create_user.php"> Tạo tài khoản</a></li>
<?php else: ?>
<li class="nav-item"><a class="nav-link"
href="../employee/dashboard.php"> Trang cá nhân</a></li>
<li class="nav-item"><a class="nav-link"
href="../employee/change_password.php">Đổi mật khẩu</a></11>
<?php endif; ?>
<li class="nav-item"><a class="nav-link text-danger"
href="../auth/logout.php">Đăng xuất</a></li>
<?php else: ?>
<li class="nav-item"><a class="nav-link"
href="../auth/login.php"> Đăng nhập</a></li>
<?php endif; ?>
</ul>
</div>
</div>
</nav>
