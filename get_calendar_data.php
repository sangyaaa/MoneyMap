<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include config
require_once dirname(__FILE__) . '/config.php';

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$userId = $_SESSION['user_id'];
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');

// Validate parameters
if ($year < 2000 || $year > 2100) $year = date('Y');
if ($month < 1 || $month > 12) $month = date('m');

// Query to get transactions
$query = "SELECT 
            DATE(date) as transaction_date,
            COUNT(*) as transaction_count,
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
          FROM transactions 
          WHERE user_id = ? 
          AND YEAR(date) = ? 
          AND MONTH(date) = ?
          GROUP BY DATE(date)
          ORDER BY transaction_date";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => 'Database prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("iii", $userId, $year, $month);
$stmt->execute();
$result = $stmt->get_result();

$calendarData = [];
while ($row = $result->fetch_assoc()) {
    $calendarData[$row['transaction_date']] = [
        'transaction_count' => (int)$row['transaction_count'],
        'total_income' => (float)$row['total_income'],
        'total_expense' => (float)$row['total_expense']
    ];
}

// Also add days with no transactions? Not needed, frontend handles it
echo json_encode($calendarData);
?>