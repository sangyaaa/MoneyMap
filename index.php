<?php
require_once 'functions.php';
requireLogin();

// Get filter values
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_account = isset($_GET['account']) ? $_GET['account'] : '';
$filter_start = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;

// Get all data
$accounts = getAccounts($conn);
$total_balance = getTotalBalance($conn);
$total_income = getTotalIncome($conn);
$total_expenses = getTotalExpenses($conn);
$budget = getBudget($conn);
$budget_remaining = $budget - $total_expenses;

// Get paginated transactions
$transactions = getTransactionsPaginated($conn, $page, $per_page, $filter_category, $filter_account, $filter_start, $filter_end, $filter_date);
$total_transactions = getTotalTransactionCount($conn, $filter_category, $filter_account, $filter_start, $filter_end, $filter_date);
$total_pages = ceil($total_transactions / $per_page);

$expense_data = getExpensesByCategory($conn);
$income_data = getIncomeByCategory($conn);

// Check if user has any accounts
$hasAccounts = count($accounts) > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyMap</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header class="main-header">
        <div class="header-container">
            <div class="logo-area">
                <a href="index.php" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="logo-text">
                        <span class="logo-main">MoneyMap</span>
                        <span class="logo-tagline">- visualize your financial journey</span>
                    </div>
                </a>
            </div>
            
            <div class="header-actions">
                <button id="darkModeToggle" onclick="toggleDarkMode()" class="action-btn theme-toggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                
                <button onclick="openModal('accountModal')" class="action-btn primary-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Account</span>
                </button>
                
                <div class="user-menu">
                    <button class="user-menu-btn">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="user-info-header">
                            <span class="user-name-header"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </button>
                    <div class="user-dropdown-menu">
                        <div class="dropdown-header">
                            <div class="dropdown-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="dropdown-info">
                                <div class="dropdown-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
                                <div class="dropdown-role">Premium Member</div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div id="alertContainer"></div>
        <div id="budgetAlertContainer"></div>
        
        <!-- No Account Warning Message -->
        <?php if (!$hasAccounts): ?>
            <div class="warning-banner">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="warning-content">
                    <strong>Welcome to MoneyMap!</strong> You don't have any accounts yet. 
                    Click the <strong>"Add Account"</strong> button above to create your first account (Bank, eSewa, Cash, etc.) and start tracking your finances.
                </div>
                <button onclick="openModal('accountModal')" class="warning-btn">Add Account Now</button>
            </div>
        <?php endif; ?>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="card">
                <h3><i class="fas fa-wallet"></i> Total Balance</h3>
                <p id="totalBalance">Rs <?php echo number_format($total_balance, 2); ?></p>
            </div>
            <div class="card income-card">
                <h3><i class="fas fa-arrow-up"></i> Total Income</h3>
                <p id="totalIncome">Rs <?php echo number_format($total_income, 2); ?></p>
            </div>
            <div class="card expense-card">
                <h3><i class="fas fa-arrow-down"></i> Total Expenses</h3>
                <p id="totalExpenses">Rs <?php echo number_format($total_expenses, 2); ?></p>
            </div>
            <div class="card budget-card" onclick="openModal('budgetModal')">
                <h3><i class="fas fa-bullseye"></i> Budget Remaining</h3>
                <p id="budgetRemaining">Rs <?php echo number_format($budget_remaining, 2); ?></p>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="charts-container">
            <div class="chart-card">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-bar"></i> Category Analysis</h3>
                    <div class="chart-toggle">
                        <button id="showExpenseBtn" class="toggle-btn active" onclick="showExpenseChart()">
                            <i class="fas fa-arrow-down"></i> Expenses
                        </button>
                        <button id="showIncomeBtn" class="toggle-btn" onclick="showIncomeChart()">
                            <i class="fas fa-arrow-up"></i> Income
                        </button>
                    </div>
                </div>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        
        <!-- Accounts Section -->
        <div class="accounts-section">
            <h3><i class="fas fa-university"></i> Your Accounts</h3>
            <?php if ($hasAccounts): ?>
                <div class="accounts-grid" id="accountsGrid">
                    <?php foreach($accounts as $account): ?>
                        <div class="account-card">
                            <h4><i class="fas fa-credit-card"></i> <?php echo htmlspecialchars($account['name']); ?></h4>
                            <div class="balance">Rs <?php echo number_format($account['balance'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-accounts">
                    <i class="fas fa-credit-card"></i>
                    <p>No accounts yet. Click "Add Account" to get started!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Add Transaction Form (Only show if accounts exist) -->
        <div class="form-container">
            <h3><i class="fas fa-plus-circle"></i> Add New Transaction</h3>
            <?php if ($hasAccounts): ?>
                <form id="addTransactionForm" class="form-group">
                    <input type="text" id="title" name="title" placeholder="📝 Title (e.g., Groceries, Salary)" required>
                    <input type="number" id="amount" name="amount" placeholder="💰 Amount in Rs" step="0.01" required>
                    <select id="category" name="category" required>
                        <option value="">📂 Select Category</option>
                        <option value="Food">🍔 Food & Dining</option>
                        <option value="Transport">🚗 Transport</option>
                        <option value="Entertainment">🎬 Entertainment</option>
                        <option value="Bills">💡 Bills & Utilities</option>
                        <option value="Shopping">🛍️ Shopping</option>
                        <option value="Health">🏥 Health & Medical</option>
                        <option value="Education">📚 Education</option>
                        <option value="Salary">💰 Salary</option>
                        <option value="Freelance">💻 Freelance</option>
                        <option value="Investment">📈 Investment</option>
                        <option value="Gift">🎁 Gift</option>
                        <option value="Other">📦 Other</option>
                    </select>
                    <select id="account_id" name="account_id" required>
                        <option value="">🏦 Select Account</option>
                        <?php foreach($accounts as $account): ?>
                            <option value="<?php echo $account['id']; ?>">
                                <?php echo htmlspecialchars($account['name']); ?> (Rs <?php echo number_format($account['balance'], 2); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="type" name="type" required>
                        <option value="expense">📉 Expense</option>
                        <option value="income">📈 Income</option>
                    </select>
                    <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Transaction</button>
                </form>
            <?php else: ?>
                <div class="disabled-form">
                    <i class="fas fa-lock"></i>
                    <p>Please add an account first to record transactions.</p>
                    <button onclick="openModal('accountModal')" class="btn-primary">Add Account</button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Calendar Section -->
        <div class="calendar-section">
            <div class="calendar-header-wrapper">
                <h3><i class="fas fa-calendar-alt"></i> Transaction Calendar</h3>
                <div class="calendar-header">
                    <button onclick="prevMonth()" class="btn-secondary"><i class="fas fa-chevron-left"></i> Prev</button>
                    <h2 id="currentMonthYear"></h2>
                    <button onclick="nextMonth()" class="btn-secondary">Next <i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            <div class="calendar-weekdays">
                <div><i class="fas fa-sun"></i> <span>Sun</span></div>
                <div><i class="fas fa-moon"></i> <span>Mon</span></div>
                <div><i class="fas fa-fire"></i> <span>Tue</span></div>
                <div><i class="fas fa-water"></i> <span>Wed</span></div>
                <div><i class="fas fa-tree"></i> <span>Thu</span></div>
                <div><i class="fas fa-cloud"></i> <span>Fri</span></div>
                <div><i class="fas fa-star"></i> <span>Sat</span></div>
            </div>
            <div id="calendarGrid" class="calendar-grid">
                <div style="grid-column: 1/-1; text-align: center; padding: 20px;">Loading calendar...</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <div class="filters-form">
                <select id="filterCategory">
                    <option value="">📂 All Categories</option>
                    <option value="Food">🍔 Food & Dining</option>
                    <option value="Transport">🚗 Transport</option>
                    <option value="Entertainment">🎬 Entertainment</option>
                    <option value="Bills">💡 Bills & Utilities</option>
                    <option value="Shopping">🛍️ Shopping</option>
                    <option value="Health">🏥 Health & Medical</option>
                    <option value="Education">📚 Education</option>
                    <option value="Salary">💰 Salary</option>
                    <option value="Freelance">💻 Freelance</option>
                    <option value="Investment">📈 Investment</option>
                    <option value="Gift">🎁 Gift</option>
                    <option value="Other">📦 Other</option>
                </select>
                <select id="filterAccount">
                    <option value="">🏦 All Accounts</option>
                    <?php foreach($accounts as $account): ?>
                        <option value="<?php echo $account['id']; ?>">
                            <?php echo htmlspecialchars($account['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="date" id="filterStartDate" placeholder="📅 Start Date">
                <input type="date" id="filterEndDate" placeholder="📅 End Date">
                <button onclick="applyFilters()" class="btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                <button onclick="resetFilters()" class="btn-secondary"><i class="fas fa-redo"></i> Reset</button>
            </div>
            
            <div id="activeFilterBanner" class="active-filter" style="display: none;"></div>
        </div>
        
        <!-- Transactions Table with Pagination -->
        <div class="transactions-section">
            <div class="transactions-header">
                <h3><i class="fas fa-list"></i> All Transactions</h3>
                <div class="transaction-count" id="transactionCount">
                    Total: <?php echo $total_transactions; ?> transactions
                </div>
            </div>
            <div class="table-responsive" id="transactionsTable">
                <!-- Transactions will be loaded here via AJAX -->
            </div>
            
            <div id="paginationContainer" class="pagination"></div>
        </div>
    </div>
    
    <!-- Add Account Modal -->
    <div id="accountModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('accountModal')">&times;</span>
            <h3><i class="fas fa-plus-circle"></i> Add New Account</h3>
            <form id="addAccountForm">
                <input type="text" id="accountName" placeholder="🏦 Account Name (e.g., Cash, Bank, eSewa)" required>
                <input type="number" id="initialBalance" placeholder="💰 Initial Balance (Rs)" step="0.01" value="0">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Create Account</button>
            </form>
        </div>
    </div>
    
    <!-- Set Budget Modal -->
    <div id="budgetModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('budgetModal')">&times;</span>
            <h3><i class="fas fa-bullseye"></i> Set Monthly Budget</h3>
            <form id="setBudgetForm">
                <input type="number" id="budgetAmount" placeholder="💰 Budget Amount (Rs)" step="0.01" value="<?php echo $budget; ?>" required>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Budget</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        let currentPage = 1;
        let currentFilters = {
            category: '',
            account: '',
            start_date: '',
            end_date: '',
            filter_date: ''
        };
        
        let categoryChart = null;
        
        const expenseCategories = <?php echo json_encode($expense_data['categories']); ?>;
        const expenseAmounts = <?php echo json_encode($expense_data['amounts']); ?>;
        const incomeCategories = <?php echo json_encode($income_data['categories']); ?>;
        const incomeAmounts = <?php echo json_encode($income_data['amounts']); ?>;
        
        $(document).ready(function() {
            loadTransactions();
            initializeCharts();
        });
        
        function initializeCharts() {
            const ctx1 = document.getElementById('incomeExpenseChart').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: ['📈 Income', '📉 Expenses'],
                    datasets: [{
                        label: 'Amount (Rs)',
                        data: [<?php echo $total_income; ?>, <?php echo $total_expenses; ?>],
                        backgroundColor: ['#27ae60', '#e74c3c'],
                        borderRadius: 10,
                        barPercentage: 0.4,
                        categoryPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rs ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rs ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            if (expenseCategories.length > 0) {
                showExpenseChart();
            }
        }
        
        function loadTransactions() {
            $.ajax({
                url: 'ajax/get_transactions.php',
                method: 'POST',
                data: {
                    page: currentPage,
                    category: currentFilters.category,
                    account: currentFilters.account,
                    start_date: currentFilters.start_date,
                    end_date: currentFilters.end_date,
                    filter_date: currentFilters.filter_date
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    $('#transactionsTable').html(data.html);
                    $('#transactionCount').html('Total: ' + data.total + ' transactions');
                    renderPagination(data.current_page, data.total_pages, data.total);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading transactions:', error);
                }
            });
        }
        
        function renderPagination(currentPage, totalPages, totalTransactions) {
            if (totalPages <= 1) {
                $('#paginationContainer').empty();
                return;
            }
            
            let paginationHtml = `
                <div class="pagination-info">
                    Showing page ${currentPage} of ${totalPages} (${totalTransactions} total transactions)
                </div>
                <div class="pagination-controls">
            `;
            
            if (currentPage > 1) {
                paginationHtml += `
                    <button onclick="goToPage(1)" class="page-btn">
                        <i class="fas fa-angle-double-left"></i> First
                    </button>
                    <button onclick="goToPage(${currentPage - 1})" class="page-btn">
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                `;
            }
            
            paginationHtml += `<div class="page-numbers">`;
            
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);
            
            for (let i = startPage; i <= endPage; i++) {
                paginationHtml += `
                    <button onclick="goToPage(${i})" class="page-number ${i === currentPage ? 'active' : ''}">
                        ${i}
                    </button>
                `;
            }
            
            paginationHtml += `</div>`;
            
            if (currentPage < totalPages) {
                paginationHtml += `
                    <button onclick="goToPage(${currentPage + 1})" class="page-btn">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                    <button onclick="goToPage(${totalPages})" class="page-btn">
                        Last <i class="fas fa-angle-double-right"></i>
                    </button>
                `;
            }
            
            paginationHtml += `</div></div>`;
            $('#paginationContainer').html(paginationHtml);
        }
        
        function goToPage(page) {
            currentPage = page;
            loadTransactions();
        }
        
        function applyFilters() {
            currentFilters.category = $('#filterCategory').val();
            currentFilters.account = $('#filterAccount').val();
            currentFilters.start_date = $('#filterStartDate').val();
            currentFilters.end_date = $('#filterEndDate').val();
            currentFilters.filter_date = '';
            currentPage = 1;
            loadTransactions();
            
            let filterText = '';
            if (currentFilters.category) filterText += `Category: ${currentFilters.category} `;
            if (currentFilters.account) filterText += `Account: ${$('#filterAccount option:selected').text()} `;
            if (currentFilters.start_date) filterText += `From: ${currentFilters.start_date} `;
            if (currentFilters.end_date) filterText += `To: ${currentFilters.end_date} `;
            
            if (filterText) {
                $('#activeFilterBanner').html(`
                    <i class="fas fa-filter"></i> Active filters: <strong>${filterText}</strong>
                    <button onclick="resetFilters()" class="clear-filter">Clear <i class="fas fa-times"></i></button>
                `).show();
            } else {
                $('#activeFilterBanner').hide();
            }
        }
        
        function resetFilters() {
            $('#filterCategory').val('');
            $('#filterAccount').val('');
            $('#filterStartDate').val('');
            $('#filterEndDate').val('');
            currentFilters = {
                category: '',
                account: '',
                start_date: '',
                end_date: '',
                filter_date: ''
            };
            currentPage = 1;
            loadTransactions();
            $('#activeFilterBanner').hide();
        }
        
        function filterByDate(date) {
            currentFilters.filter_date = date;
            currentFilters.category = '';
            currentFilters.account = '';
            currentFilters.start_date = '';
            currentFilters.end_date = '';
            currentPage = 1;
            loadTransactions();
            
            $('#activeFilterBanner').html(`
                <i class="fas fa-calendar-day"></i> Showing transactions for: <strong>${date}</strong>
                <button onclick="resetFilters()" class="clear-filter">Clear <i class="fas fa-times"></i></button>
            `).show();
        }
        
        $('#addTransactionForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                action: 'add_transaction',
                title: $('#title').val(),
                amount: $('#amount').val(),
                category: $('#category').val(),
                account_id: $('#account_id').val(),
                type: $('#type').val(),
                date: $('#date').val()
            };
            
            $.ajax({
                url: 'ajax/process_transaction.php',
                method: 'POST',
                data: formData,
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        showAlert('success', data.message);
                        $('#addTransactionForm')[0].reset();
                        loadTransactions();
                        renderCalendar();
                        updateSummary();
                    } else {
                        showAlert('error', data.error);
                    }
                },
                error: function() {
                    showAlert('error', 'Failed to add transaction');
                }
            });
        });
        
        function deleteTransaction(id) {
            if (confirm('Are you sure you want to delete this transaction?')) {
                $.ajax({
                    url: 'ajax/process_transaction.php',
                    method: 'POST',
                    data: {
                        action: 'delete_transaction',
                        transaction_id: id
                    },
                    success: function(response) {
                        const data = JSON.parse(response);
                        if (data.success) {
                            showAlert('success', data.message);
                            loadTransactions();
                            renderCalendar();
                            updateSummary();
                        } else {
                            showAlert('error', data.error);
                        }
                    }
                });
            }
        }
        
        $('#addAccountForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'ajax/process_account.php',
                method: 'POST',
                data: {
                    action: 'add_account',
                    account_name: $('#accountName').val(),
                    initial_balance: $('#initialBalance').val()
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        showAlert('success', data.message);
                        closeModal('accountModal');
                        $('#addAccountForm')[0].reset();
                        location.reload();
                    } else {
                        showAlert('error', data.error);
                    }
                }
            });
        });
        
        $('#setBudgetForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'ajax/process_budget.php',
                method: 'POST',
                data: {
                    budget: $('#budgetAmount').val()
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        showAlert('success', data.message);
                        closeModal('budgetModal');
                        updateSummary();
                    } else {
                        showAlert('error', data.error);
                    }
                }
            });
        });
        
        function updateSummary() {
            $.ajax({
                url: 'ajax/get_summary.php',
                method: 'GET',
                success: function(response) {
                    const data = JSON.parse(response);
                    $('#totalBalance').text('Rs ' + data.total_balance);
                    $('#totalIncome').text('Rs ' + data.total_income);
                    $('#totalExpenses').text('Rs ' + data.total_expenses);
                    $('#budgetRemaining').text('Rs ' + data.budget_remaining);
                }
            });
        }
        
        function showAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type}">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    ${message}
                </div>
            `;
            $('#alertContainer').html(alertHtml);
            setTimeout(() => {
                $('.alert').fadeOut();
            }, 3000);
        }
        
        function showExpenseChart() {
            if (categoryChart) categoryChart.destroy();
            
            const ctx = document.getElementById('categoryChart').getContext('2d');
            categoryChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: expenseCategories,
                    datasets: [{
                        label: 'Expenses by Category (Rs)',
                        data: expenseAmounts,
                        backgroundColor: '#e74c3c',
                        borderRadius: 8,
                        barPercentage: 0.5,
                        categoryPercentage: 0.7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: { display: true, text: '📊 Expense Distribution' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rs ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            $('#showExpenseBtn').addClass('active');
            $('#showIncomeBtn').removeClass('active');
        }
        
        function showIncomeChart() {
            if (categoryChart) categoryChart.destroy();
            
            const ctx = document.getElementById('categoryChart').getContext('2d');
            categoryChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: incomeCategories,
                    datasets: [{
                        label: 'Income by Category (Rs)',
                        data: incomeAmounts,
                        backgroundColor: '#27ae60',
                        borderRadius: 8,
                        barPercentage: 0.5,
                        categoryPercentage: 0.7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: { display: true, text: '📊 Income Distribution' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rs ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            $('#showIncomeBtn').addClass('active');
            $('#showExpenseBtn').removeClass('active');
        }
        
        window.goToPage = goToPage;
        window.applyFilters = applyFilters;
        window.resetFilters = resetFilters;
        window.filterByDate = filterByDate;
        window.deleteTransaction = deleteTransaction;
        window.showExpenseChart = showExpenseChart;
        window.showIncomeChart = showIncomeChart;
    </script>
</body>
</html>