<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT e.name, e.phone, e.email, e.position, e.department 
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

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $position = trim($_POST["position"]);
    $department = trim($_POST["department"]);

    $valid_prefixes = ["032", "033", "034", "035", "036", "037", "038", "039", // Viettel
                       "070", "076", "077", "078", "079",                         // MobiFone
                       "083", "084", "085", "081", "082",                         // VinaPhone
                       "056", "058",                                               // Vietnamobile
                       "059",                                                     // Gmobile
                       "090", "093", "089", "091", "094", "088"];

    $prefix = substr($phone, 0, 3);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email không hợp lệ.";
    } elseif (!preg_match('/^0[0-9]{9}$/', $phone)) {
        $error_message = "Số điện thoại phải có đúng 10 chữ số và bắt đầu bằng số 0.";
    } elseif (!in_array($prefix, $valid_prefixes)) {
        $error_message = "Đầu số điện thoại không hợp lệ theo nhà mạng Việt Nam.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE employees 
                                   SET phone = ?, email = ?, position = ?, department = ? 
                                   WHERE employee_id = (SELECT employee_id FROM users WHERE user_id = ?)");
            $stmt->execute([$phone, $email, $position, $department, $_SESSION["user_id"]]);

            header("Location: profile.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Lỗi cập nhật: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cập nhật thông tin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
    <h2 class="text-center">Cập nhật thông tin cá nhân</h2>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4">
        <div class="mb-3">
            <label class="form-label"><strong>Họ và Tên:</strong></label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user["name"]); ?>" disabled>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label"><strong>Số điện thoại:</strong></label>
            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user["phone"]); ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label"><strong>Email:</strong></label>
            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user["email"]); ?>" required>
        </div>

        <div class="mb-3">
            <label for="position" class="form-label"><strong>Vị trí:</strong></label>
            <input type="text" class="form-control" name="position" value="<?php echo htmlspecialchars($user["position"]); ?>" required>
        </div>

        <div class="mb-3">
            <label for="department" class="form-label"><strong>Phòng ban:</strong></label>
            <input type="text" class="form-control" name="department" value="<?php echo htmlspecialchars($user["department"]); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        <a href="profile.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
