<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT e.name, e.phone, e.email, e.position, 
                                  e.department, e.avatar, u.username, u.role 
                           FROM employees e 
                           JOIN users u ON e.employee_id = u.employee_id 
                           WHERE u.user_id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<p class='text-danger text-center'>Không tìm thấy thông tin cá nhân.</p>";
        exit();
    }

} catch (PDOException $e) {
    die("Lỗi: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thông tin cá nhân</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        .profile-container {
            max-width: 700px;
            margin: 3rem auto;
        }
        .profile-card {
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .profile-actions a {
            margin: 0 0.5rem;
        }
    </style>
</head>
<body>

<?php include "../includes/header.php"; ?>

<div class="container profile-container">
    <div class="card p-4 profile-card">
        <h3 class="text-center mb-4">Thông tin cá nhân</h3>
        <div class="text-center mb-4">
            <img src="../uploads/avatar/<?php echo !empty($user["avatar"]) ? htmlspecialchars($user["avatar"]) : 'default-avatar.png'; ?>" 
                 alt="Ảnh đại diện" class="profile-avatar">
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <p><strong>Họ và Tên:</strong> <?= htmlspecialchars($user["name"]) ?></p>
                <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($user["phone"]) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user["email"]) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Vị trí:</strong> <?= htmlspecialchars($user["position"]) ?></p>
                <p><strong>Phòng ban:</strong> <?= htmlspecialchars($user["department"]) ?></p>
                <p><strong>Tên đăng nhập:</strong> <?= htmlspecialchars($user["username"]) ?></p>
                <p><strong>Quyền:</strong> <?= htmlspecialchars($user["role"]) ?></p>
            </div>
        </div>
        <div class="text-center mt-4 profile-actions">
            <a href="edit_profile.php" class="btn btn-primary">Cập nhật thông tin</a>
            <a href="upload_avatar.php" class="btn btn-outline-warning">Đổi ảnh đại diện</a>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
