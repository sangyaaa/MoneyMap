<?php
session_start();
require_once '../functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$action = $_POST['action'];

if ($action == 'add_account') {
    $name = $_POST['account_name'];
    $balance = $_POST['initial_balance'];
    
    if (addAccount($conn, $name, $balance)) {
        echo json_encode(['success' => true, 'message' => 'Account added successfully!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add account!']);
    }
}
?>