<?php
require_once 'config.php';

// Get all accounts for current user
function getAccounts($conn) {
    $userId = getCurrentUserId();
    if (!$userId) return [];
    
    $stmt = $conn->prepare("SELECT * FROM accounts WHERE user_id = ? ORDER BY id");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $accounts = [];
    while ($row = $result->fetch_assoc()) {
        $accounts[] = $row;
    }
    return $accounts;
}

// Get total balance for current user
function getTotalBalance($conn) {
    $userId = getCurrentUserId();
    if (!$userId) return 0;
    
    $stmt = $conn->prepare("SELECT SUM(balance) as total FROM accounts WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Get total income for current user
function getTotalIncome($conn) {
    $userId = getCurrentUserId();
    if (!$userId) return 0;
    
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM transactions WHERE type = 'income' AND user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Get total expenses for current user
function getTotalExpenses($conn) {
    $userId = getCurrentUserId();
    if (!$userId) return 0;
    
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM transactions WHERE type = 'expense' AND user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Get budget for current user
function getBudget($conn) {
    $userId = getCurrentUserId();
    if (!$userId) return 0;
    
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'initial_budget' AND user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['setting_value'] ?? 0;
}

// Get transactions with filters for current user
function getTransactions($conn, $category = '', $account_id = '', $start_date = '', $end_date = '') {
    $userId = getCurrentUserId();
    if (!$userId) return [];
    
    $sql = "SELECT t.*, a.name as account_name 
            FROM transactions t 
            JOIN accounts a ON t.account_id = a.id 
            WHERE t.user_id = ?";
    $params = [$userId];
    $types = "i";
    
    if (!empty($category)) {
        $sql .= " AND t.category = ?";
        $params[] = $category;
        $types .= "s";
    }
    if (!empty($account_id)) {
        $sql .= " AND t.account_id = ?";
        $params[] = $account_id;
        $types .= "i";
    }
    if (!empty($start_date)) {
        $sql .= " AND t.date >= ?";
        $params[] = $start_date;
        $types .= "s";
    }
    if (!empty($end_date)) {
        $sql .= " AND t.date <= ?";
        $params[] = $end_date;
        $types .= "s";
    }
    
    $sql .= " ORDER BY t.date DESC, t.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    return $transactions;
}

// Get monthly transactions for calendar for current user
function getMonthlyTransactions($conn, $year, $month) {
    $userId = getCurrentUserId();
    if (!$userId) return [];
    
    $query = "SELECT 
                DATE(date) as transaction_date,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
              FROM transactions 
              WHERE user_id = ? 
              AND YEAR(date) = ? 
              AND MONTH(date) = ?
              GROUP BY DATE(date)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $userId, $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['transaction_date']] = [
            'transaction_count' => intval($row['transaction_count']),
            'total_income' => floatval($row['total_income']),
            'total_expense' => floatval($row['total_expense'])
        ];
    }
    return $data;
}

// Get expenses by category for chart for current user
function getExpensesByCategory($conn) {
    $userId = getCurrentUserId();
    if (!$userId) return ['categories' => [], 'amounts' => []];
    
    $stmt = $conn->prepare("SELECT category, SUM(amount) as total FROM transactions WHERE type = 'expense' AND user_id = ? GROUP BY category");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    $amounts = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
        $amounts[] = $row['total'];
    }
    return ['categories' => $categories, 'amounts' => $amounts];
}

// Get income by category for chart for current user
function getIncomeByCategory($conn) {
    $userId = getCurrentUserId();
    if (!$userId) return ['categories' => [], 'amounts' => []];
    
    $stmt = $conn->prepare("SELECT category, SUM(amount) as total FROM transactions WHERE type = 'income' AND user_id = ? GROUP BY category");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    $amounts = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
        $amounts[] = $row['total'];
    }
    return ['categories' => $categories, 'amounts' => $amounts];
}

// Add account for current user
function addAccount($conn, $name, $initial_balance) {
    $userId = getCurrentUserId();
    if (!$userId) return false;
    
    $stmt = $conn->prepare("INSERT INTO accounts (name, balance, user_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $name, $initial_balance, $userId);
    return $stmt->execute();
}

// Add transaction for current user
function addTransaction($conn, $title, $amount, $category, $account_id, $type, $date) {
    $userId = getCurrentUserId();
    if (!$userId) return false;
    
    $conn->begin_transaction();
    
    try {
        // Insert transaction
        $stmt = $conn->prepare("INSERT INTO transactions (title, amount, category, account_id, type, date, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsissi", $title, $amount, $category, $account_id, $type, $date, $userId);
        $stmt->execute();
        
        // Update account balance
        $balance_change = ($type == 'income') ? $amount : -$amount;
        $update = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ? AND user_id = ?");
        $update->bind_param("dii", $balance_change, $account_id, $userId);
        $update->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Delete transaction for current user
function deleteTransaction($conn, $id) {
    $userId = getCurrentUserId();
    if (!$userId) return false;
    
    // Get transaction details
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();
    $transaction = $stmt->get_result()->fetch_assoc();
    
    if (!$transaction) return false;
    
    $conn->begin_transaction();
    
    try {
        // Revert balance
        $balance_change = ($transaction['type'] == 'income') ? -$transaction['amount'] : $transaction['amount'];
        $update = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ? AND user_id = ?");
        $update->bind_param("dii", $balance_change, $transaction['account_id'], $userId);
        $update->execute();
        
        // Delete transaction
        $delete = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        $delete->bind_param("ii", $id, $userId);
        $delete->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Update budget for current user
function updateBudget($conn, $budget) {
    $userId = getCurrentUserId();
    if (!$userId) return false;
    
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, user_id) VALUES ('initial_budget', ?, ?) 
                            ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("dii", $budget, $userId, $budget);
    return $stmt->execute();
}

// Get transactions with pagination for current user
function getTransactionsPaginated($conn, $page = 1, $per_page = 5, $category = '', $account_id = '', $start_date = '', $end_date = '', $specific_date = '') {
    $userId = getCurrentUserId();
    if (!$userId) return [];
    
    $offset = ($page - 1) * $per_page;
    
    $sql = "SELECT t.*, a.name as account_name 
            FROM transactions t 
            JOIN accounts a ON t.account_id = a.id 
            WHERE t.user_id = ?";
    $params = [$userId];
    $types = "i";
    
    if (!empty($category)) {
        $sql .= " AND t.category = ?";
        $params[] = $category;
        $types .= "s";
    }
    if (!empty($account_id)) {
        $sql .= " AND t.account_id = ?";
        $params[] = $account_id;
        $types .= "i";
    }
    if (!empty($start_date)) {
        $sql .= " AND t.date >= ?";
        $params[] = $start_date;
        $types .= "s";
    }
    if (!empty($end_date)) {
        $sql .= " AND t.date <= ?";
        $params[] = $end_date;
        $types .= "s";
    }
    if (!empty($specific_date)) {
        $sql .= " AND t.date = ?";
        $params[] = $specific_date;
        $types .= "s";
    }
    
    $sql .= " ORDER BY t.date DESC, t.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    return $transactions;
}

// Get total transaction count for current user
function getTotalTransactionCount($conn, $category = '', $account_id = '', $start_date = '', $end_date = '', $specific_date = '') {
    $userId = getCurrentUserId();
    if (!$userId) return 0;
    
    $sql = "SELECT COUNT(*) as total 
            FROM transactions t 
            WHERE t.user_id = ?";
    $params = [$userId];
    $types = "i";
    
    if (!empty($category)) {
        $sql .= " AND t.category = ?";
        $params[] = $category;
        $types .= "s";
    }
    if (!empty($account_id)) {
        $sql .= " AND t.account_id = ?";
        $params[] = $account_id;
        $types .= "i";
    }
    if (!empty($start_date)) {
        $sql .= " AND t.date >= ?";
        $params[] = $start_date;
        $types .= "s";
    }
    if (!empty($end_date)) {
        $sql .= " AND t.date <= ?";
        $params[] = $end_date;
        $types .= "s";
    }
    if (!empty($specific_date)) {
        $sql .= " AND t.date = ?";
        $params[] = $specific_date;
        $types .= "s";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}
?>