<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../auth/login.php");
    exit();
}

$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$query_salary_details = "SELECT e.employee_id, e.name, p.total_hours, p.base_salary,
    COALESCE(SUM(CASE WHEN bp.type = 'Thưởng' THEN bp.amount ELSE 0 END), 0) AS total_bonus,
    COALESCE(SUM(CASE WHEN bp.type = 'Phạt' THEN bp.amount ELSE 0 END), 0) AS total_penalty,
    p.total_salary, p.status
FROM payroll p
JOIN employees e ON p.employee_id = e.employee_id
LEFT JOIN bonuses_penalties bp ON p.employee_id = bp.employee_id AND bp.month = p.month AND bp.year = p.year
WHERE p.month = ? AND p.year = ?
GROUP BY e.employee_id, e.name, p.total_hours, p.base_salary, p.total_salary, p.status";

$stmt = $pdo->prepare($query_salary_details);
$stmt->execute([$month, $year]);
$salary_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($salary_details)) {
    die("Không có dữ liệu lương cho tháng $month/$year.");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', "BÁO CÁO LƯƠNG THÁNG $month/$year");
$sheet->mergeCells('A1:H1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$columns = ['Mã NV', 'Họ và Tên', 'Số giờ làm', 'Lương cơ bản', 'Tổng thưởng', 'Tổng phạt', 'Lương thực nhận', 'Trạng thái'];
$columnLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

foreach ($columns as $index => $columnName) {
    $sheet->setCellValue($columnLetters[$index] . '3', $columnName);
    $sheet->getStyle($columnLetters[$index] . '3')->getFont()->setBold(true);
    $sheet->getColumnDimension($columnLetters[$index])->setAutoSize(true);
}

$rowNumber = 4;
foreach ($salary_details as $row) {
    $sheet->setCellValue("A$rowNumber", $row['employee_id']);
    $sheet->setCellValue("B$rowNumber", $row['name']);
    $sheet->setCellValue("C$rowNumber", $row['total_hours'] . " giờ");
    $sheet->setCellValue("D$rowNumber", number_format($row['base_salary'], 0, ',', '.') . " VND");
    $sheet->setCellValue("E$rowNumber", number_format($row['total_bonus'], 0, ',', '.') . " VND");
    $sheet->setCellValue("F$rowNumber", number_format($row['total_penalty'], 0, ',', '.') . " VND");
    $sheet->setCellValue("G$rowNumber", number_format($row['total_salary'], 0, ',', '.') . " VND");
    $sheet->setCellValue("H$rowNumber", $row['status']);
    $rowNumber++;
}

$filename = "Bao_cao_luong_{$month}_{$year}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
