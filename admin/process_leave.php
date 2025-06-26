<?php 
session_start(); 
require_once "../config/database.php"; 

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") { 
    header("Location: ../auth/login.php"); 
    exit(); 
} 

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["leave_id"], $_POST["action"])) { 
    $leave_id = $_POST["leave_id"]; 
    $action = $_POST["action"]; 
    $admin_id = $_SESSION["user_id"]; 

    if ($action === "approve") { 
        $status = "Đã duyệt"; 
    } elseif ($action === "reject") { 
        $status = "Bị từ chối"; 
    } else { 
        die("Lỗi: Hành động không hợp lệ."); 
    } 

    try { 
        $query = "UPDATE leaves SET status = ?, approved_by = ? WHERE leave_id = ?"; 
        $stmt = $pdo->prepare($query); 
        $stmt->execute([$status, $admin_id, $leave_id]); 

        if ($stmt->rowCount() > 0) { 
            header("Location: manage_leaves.php?success=1"); 
            exit(); 
        } else { 
            die("Lỗi: Không có dòng nào được cập nhật."); 
        }
    } catch (PDOException $e) { 
        die("Lỗi: " . $e->getMessage()); 
    } 
} else { 
    header("Location: manage_leaves.php"); 
    exit(); 
}
?>
