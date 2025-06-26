<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

// Lấy avatar hiện tại của nhân viên
try {
    $stmt = $pdo->prepare("SELECT avatar FROM employees 
                           WHERE employee_id = (SELECT employee_id FROM users WHERE user_id = ?)");
    $stmt->execute([$_SESSION["user_id"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $avatar = !empty($user["avatar"]) ? "../uploads/avatar/" . htmlspecialchars($user["avatar"]) : "../assets/img/default-avatar.png";
} catch (PDOException $e) {
    die("Lỗi: " . htmlspecialchars($e->getMessage()));
}

$error_message = "";
$success_message = "";

// Xử lý khi nhân viên upload ảnh
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["avatar"])) {
    $uploadDir = "../uploads/avatar/";
    $fileTmp = $_FILES["avatar"]["tmp_name"];
    $fileSize = $_FILES["avatar"]["size"];
    $fileExt = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));

    $allowedExts = ["jpg", "jpeg", "png"];
    $maxFileSize = 2 * 1024 * 1024; // Giới hạn 2MB

    // Tạo tên file an toàn dựa trên user_id
    $newFileName = "avatar_" . $_SESSION["user_id"] . "." . $fileExt;
    $targetFile = $uploadDir . $newFileName;

    // Kiểm tra định dạng file
    if (!in_array($fileExt, $allowedExts)) {
        $error_message = "Chỉ chấp nhận file JPG, JPEG, PNG.";
    } elseif ($fileSize > $maxFileSize) {
        $error_message = "File quá lớn, chỉ cho phép dưới 2MB.";
    } else {
        // Xóa ảnh cũ nếu có
        if (!empty($user["avatar"]) && file_exists($uploadDir . $user["avatar"])) {
            unlink($uploadDir . $user["avatar"]);
        }

        // Upload ảnh mới và cập nhật database
        if (move_uploaded_file($fileTmp, $targetFile)) {
            try {
                $stmt = $pdo->prepare("UPDATE employees 
                                       SET avatar = ? 
                                       WHERE employee_id = (SELECT employee_id FROM users WHERE user_id = ?)");
                $stmt->execute([$newFileName, $_SESSION["user_id"]]);

                $success_message = "Cập nhật ảnh đại diện thành công!";
                $avatar = $targetFile; // Hiển thị ảnh mới ngay
            } catch (PDOException $e) {
                $error_message = "Lỗi cập nhật: " . htmlspecialchars($e->getMessage());
            }
        } else {
            $error_message = "Lỗi khi tải lên ảnh.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cập nhật ảnh đại diện</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
    <h2 class="text-center">Cập nhật ảnh đại diện</h2>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php elseif (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <div class="text-center">
        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Ảnh đại diện" class="rounded-circle" width="150" height="150">
    </div>

    <form method="POST" enctype="multipart/form-data" class="card p-4 mt-3">
        <div class="mb-3">
            <label for="avatar" class="form-label"><strong>Chọn ảnh mới:</strong></label>
            <input type="file" class="form-control" name="avatar" accept=".jpg, .jpeg, .png" required>
        </div>
        <button type="submit" class="btn btn-primary">Tải lên</button>
        <a href="profile.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
