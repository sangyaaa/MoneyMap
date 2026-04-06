<?php
session_start();
require_once '../functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$action = $_POST['action'];

if ($action == 'add_transaction') {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $account_id = $_POST['account_id'];
    $type = $_POST['type'];
    $date = $_POST['date'];
    
    if (addTransaction($conn, $title, $amount, $category, $account_id, $type, $date)) {
        echo json_encode(['success' => true, 'message' => 'Transaction added successfully!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add transaction!']);
    }
} elseif ($action == 'delete_transaction') {
    $id = $_POST['transaction_id'];
    
    if (deleteTransaction($conn, $id)) {
        echo json_encode(['success' => true, 'message' => 'Transaction deleted successfully!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete transaction!']);
    }
}
?>