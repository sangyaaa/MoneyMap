<?php
session_start();
require_once '../functions.php';

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$total_balance = getTotalBalance($conn);
$total_income = getTotalIncome($conn);
$total_expenses = getTotalExpenses($conn);
$budget = getBudget($conn);
$budget_remaining = $budget - $total_expenses;

echo json_encode([
    'total_balance' => number_format($total_balance, 2),
    'total_income' => number_format($total_income, 2),
    'total_expenses' => number_format($total_expenses, 2),
    'budget_remaining' => number_format($budget_remaining, 2)
]);
?>