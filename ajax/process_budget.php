<?php
session_start();
require_once '../functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$budget = $_POST['budget'];

if (updateBudget($conn, $budget)) {
    echo json_encode(['success' => true, 'message' => 'Budget updated successfully!']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update budget!']);
}
?>