<?php
session_start();
require_once '../functions.php';

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$per_page = 5;
$category = isset($_POST['category']) ? $_POST['category'] : '';
$account = isset($_POST['account']) ? $_POST['account'] : '';
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$filter_date = isset($_POST['filter_date']) ? $_POST['filter_date'] : '';

$transactions = getTransactionsPaginated($conn, $page, $per_page, $category, $account, $start_date, $end_date, $filter_date);
$total = getTotalTransactionCount($conn, $category, $account, $start_date, $end_date, $filter_date);
$total_pages = ceil($total / $per_page);

ob_start();
if (count($transactions) > 0): ?>
    <table>
        <thead>
            <tr>
                <th><i class="fas fa-tag"></i> Title</th>
                <th><i class="fas fa-money-bill"></i> Amount</th>
                <th><i class="fas fa-folder"></i> Category</th>
                <th><i class="fas fa-building"></i> Account</th>
                <th><i class="fas fa-chart-line"></i> Type</th>
                <th><i class="fas fa-calendar"></i> Date</th>
                <th><i class="fas fa-cog"></i> Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($transactions as $transaction): ?>
                <tr class="<?php echo $transaction['type'] == 'income' ? 'income-row' : 'expense-row'; ?>">
                    <td><i class="fas fa-receipt"></i> <?php echo htmlspecialchars($transaction['title']); ?></td>
                    <td style="color: <?php echo $transaction['type'] == 'income' ? '#27ae60' : '#e74c3c'; ?>; font-weight: bold;">
                        <?php echo $transaction['type'] == 'income' ? '+' : '-'; ?> Rs <?php echo number_format(abs($transaction['amount']), 2); ?>
                    </td>
                    <td><?php 
                        $icon = '';
                        switch($transaction['category']) {
                            case 'Food': $icon = '🍔 '; break;
                            case 'Transport': $icon = '🚗 '; break;
                            case 'Entertainment': $icon = '🎬 '; break;
                            case 'Bills': $icon = '💡 '; break;
                            case 'Shopping': $icon = '🛍️ '; break;
                            case 'Health': $icon = '🏥 '; break;
                            case 'Education': $icon = '📚 '; break;
                            case 'Salary': $icon = '💰 '; break;
                            case 'Freelance': $icon = '💻 '; break;
                            case 'Investment': $icon = '📈 '; break;
                            case 'Gift': $icon = '🎁 '; break;
                            default: $icon = '📁 ';
                        }
                        echo $icon . htmlspecialchars($transaction['category']); 
                    ?></td>
                    <td><i class="fas fa-building"></i> <?php echo htmlspecialchars($transaction['account_name']); ?></td>
                    <td><?php echo $transaction['type'] == 'income' ? '<span style="color:#27ae60"><i class="fas fa-arrow-up"></i> Income</span>' : '<span style="color:#e74c3c"><i class="fas fa-arrow-down"></i> Expense</span>'; ?></td>
                    <td><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($transaction['date'])); ?></td>
                    <td>
                        <button onclick="deleteTransaction(<?php echo $transaction['id']; ?>)" class="btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align: center; padding: 40px;">
        <i class="fas fa-inbox"></i> No transactions found. Add your first transaction!
    </p>
<?php endif;
$html = ob_get_clean();

echo json_encode([
    'html' => $html,
    'total' => $total,
    'current_page' => $page,
    'total_pages' => $total_pages
]);
?>