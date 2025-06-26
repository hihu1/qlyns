<?php
session_start();
// Kiểm tra quyền truy cập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; }
        body { display: flex; flex-direction: column; overflow-x: hidden; }
        /* Sidebar fixed full height */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 240px;
            background-color: #343a40;
            padding: 1rem;
            transition: transform 0.3s ease;
        }
        #sidebar.collapsed { transform: translateX(-100%); }
        #sidebar .nav-link {
            color: #adb5bd;
            transition: color 0.2s, background-color 0.2s, padding-left 0.2s;
        }
        #sidebar .nav-link:hover {
            color: #fff;
            background-color: #343a40;
            padding-left: 1.5rem;
        }
        #sidebar .nav-link.active {
            color: #fff;
            background-color: #343a40;
        }
        /* Toggle button */
        #toggleBtn {
            position: fixed;
            top: 0.5rem;
            left: 0.25rem; /* adjust as needed */
            margin-left: -0.25rem; /* shift half width to the left */
            z-index: 1050;
            border: none;
            background: #343a40;
            color: #fff;
            padding: 0.5rem;
            border-radius: 0.25rem;
        }
        /* Wrapper content */
        .main-wrapper { display: flex; flex: 1; }
        .content { margin-left: 240px; flex: 1; padding: 2rem; }
        /* Footer */
        footer { flex-shrink: 0; }
        
    </style>
</head>
<body>
    <button id="toggleBtn"><i class="bi bi-list fs-3"></i></button>
    <div class="main-wrapper">
        <nav id="sidebar">
            <h4 class="text-white text-center mb-4">Admin Menu</h4>
            <ul class="nav nav-pills flex-column mb-auto">
                <li><a href="dashboard.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='dashboard.php')?' active':'' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="create_user.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='create_user.php')?' active':'' ?>"><i class="bi bi-person-plus"></i> Tạo tài khoản</a></li>
                <hr class="text-secondary">
                <li><a href="manage_employees.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='manage_employees.php')?' active':'' ?>"><i class="bi bi-people"></i> Nhân viên</a></li>
                <li><a href="manage_attendance.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='manage_attendance.php')?' active':'' ?>"><i class="bi bi-calendar-check"></i> Chấm công</a></li>
                <li><a href="update_payroll.php?redirect=manage_payroll.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='update_payroll.php')?' active':'' ?>"><i class="bi bi-currency-dollar"></i> Lương</a></li>
                <li><a href="manage_base_salary.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='manage_base_salary.php')?' active':'' ?>"><i class="bi bi-sliders"></i> Lương Cơ bản</a></li>
                <li><a href="manage_leaves.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='manage_leaves.php')?' active':'' ?>"><i class="bi bi-envelope-open"></i> Nghỉ phép</a></li>
                <li><a href="manage_bonuses.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='manage_bonuses.php')?' active':'' ?>"><i class="bi bi-award"></i> Thưởng/Phạt</a></li>
                <li><a href="reports.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='reports.php')?' active':'' ?>"><i class="bi bi-bar-chart"></i> Báo cáo</a></li>
                <hr class="text-secondary">
                <li><a href="../auth/login.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
            </ul>
        </nav>
        <div class="content">
            <h2 class="text-center mb-4">Bảng điều khiển Admin</h2>
            <p class="text-center mb-5">Xin chào, <strong><?= htmlspecialchars($_SESSION['username']??'Admin') ?></strong>! Chọn chức năng từ menu bên trái.</p>
            <div class="row g-4">
                <!-- Dashboard content cards reused? Optional content -->
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });
    </script>
</body>
</html>
