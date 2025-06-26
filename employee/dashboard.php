<?php
session_start();
require_once "../config/database.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Nhân viên') {
    header('Location: ../auth/login.php');
    exit();
}
$employee_id = $_SESSION['employee_id'] ?? null;
if (!$employee_id) die('Lỗi: Không tìm thấy ID nhân viên trong session.');
$stmt = $pdo->prepare('SELECT name, phone, email, avatar FROM employees WHERE employee_id = ?');
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC) ?: die('Lỗi: Không tìm thấy thông tin nhân viên.');
$avatar = !empty($employee['avatar']) ? $employee['avatar'] : 'default.png';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bảng điều khiển Nhân viên</title>
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
            bottom: 0; /* ensures reaches exactly footer */
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
            background-color: rgba(255,255,255,0.15);
            padding-left: 1.5rem;
        }
        #sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }
        /* Toggle button */
        #toggleBtn {
            position: fixed;
            top: 0.5rem;
            left: 0.5rem;
            z-index: 1050;
            border: none;
            background: #343a40;
            color: #fff;
            padding: 0.5rem;
            border-radius: 0.25rem;
        }
        /* Wrapper content pushes aside sidebar */
        .main-wrapper { display: flex; flex: 1; margin-top: 0; }
        .content { margin-left: 240px; flex: 1; padding: 2rem; }
        .avatar { width: 80px; height: 80px; object-fit: cover; }
        /* Footer at bottom */
        footer { flex-shrink: 0; }
    </style>
</head>
<body>
    <button id="toggleBtn"><i class="bi bi-list fs-3"></i></button>
    <div class="main-wrapper">
        <nav id="sidebar">
            <h4 class="text-white text-center mb-4">Nhân viên</h4>
            <ul class="nav nav-pills flex-column mb-auto">
                <li><a href="dashboard.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='dashboard.php') ? ' active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="profile.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='profile.php') ? ' active' : '' ?>"><i class="bi bi-person-circle"></i> Hồ sơ của tôi</a></li>
                <li><a href="attendance.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='attendance.php') ? ' active' : '' ?>"><i class="bi bi-calendar-check"></i> Chấm công</a></li>
                <li><a href="change_password.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='change_password.php') ? ' active' : '' ?>"><i class="bi bi-lock"></i> Đổi mật khẩu</a></li>
                <li><a href="request_leave.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='request_leave.php') ? ' active' : '' ?>"><i class="bi bi-envelope-open"></i> Nghỉ phép</a></li>
                <li><a href="salary.php" class="nav-link<?= (basename($_SERVER['PHP_SELF'])==='salary.php') ? ' active' : '' ?>"><i class="bi bi-currency-dollar"></i> Lương & Thưởng</a></li>
                <hr class="text-secondary">
                <li><a href="../auth/login.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
            </ul>
        </nav>
        <div class="content">
            <h2 class="text-center mb-4">Bảng điều khiển Nhân viên</h2>
            <p class="text-center mb-5">Xin chào, <strong><?= htmlspecialchars($employee['name']) ?></strong>! Đây là trang điều khiển của bạn.</p>
            <div class="row justify-content-center g-4">
                <div class="col-sm-6 col-md-4">
                    
                </div>
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
