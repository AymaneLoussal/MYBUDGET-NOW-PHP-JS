<?php
session_start();

require_once 'includes/db.php';

$pageTitle = "Dashboard - MyBudget";

$total_income = 0;
$total_expense = 0;
$balance = 0;

$income_result = mysqli_query($conn, "SELECT SUM(amount) as total FROM incomes");
if ($income_result) {
    $income_row = mysqli_fetch_assoc($income_result);
    $total_income = $income_row['total'] ? floatval($income_row['total']) : 0;
}

$expense_result = mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses");
if ($expense_result) {
    $expense_row = mysqli_fetch_assoc($expense_result);
    $total_expense = $expense_row['total'] ? floatval($expense_row['total']) : 0;
}

$balance = $total_income - $total_expense;

$current_month = date('Y-m');
$month_income = 0;
$month_expense = 0;

$month_income_result = mysqli_query($conn, 
    "SELECT SUM(amount) as total FROM incomes 
     WHERE DATE_FORMAT(date, '%Y-%m') = '$current_month'");
if ($month_income_result) {
    $row = mysqli_fetch_assoc($month_income_result);
    $month_income = $row['total'] ? floatval($row['total']) : 0;
}

$month_expense_result = mysqli_query($conn, 
    "SELECT SUM(amount) as total FROM expenses 
     WHERE DATE_FORMAT(date, '%Y-%m') = '$current_month'");
if ($month_expense_result) {
    $row = mysqli_fetch_assoc($month_expense_result);
    $month_expense = $row['total'] ? floatval($row['total']) : 0;
}

$month_balance = $month_income - $month_expense;

// Last Month Data
$last_month = date('Y-m', strtotime('-1 month'));
$last_month_income = 0;
$last_month_expense = 0;

$last_month_income_result = mysqli_query($conn, 
    "SELECT SUM(amount) as total FROM incomes 
     WHERE DATE_FORMAT(date, '%Y-%m') = '$last_month'");
if ($last_month_income_result) {
    $row = mysqli_fetch_assoc($last_month_income_result);
    $last_month_income = $row['total'] ? floatval($row['total']) : 0;
}

$last_month_expense_result = mysqli_query($conn, 
    "SELECT SUM(amount) as total FROM expenses 
     WHERE DATE_FORMAT(date, '%Y-%m') = '$last_month'");
if ($last_month_expense_result) {
    $row = mysqli_fetch_assoc($last_month_expense_result);
    $last_month_expense = $row['total'] ? floatval($row['total']) : 0;
}

// Expense Categories
$categories_result = mysqli_query($conn, 
    "SELECT category, SUM(amount) as total 
     FROM expenses 
     GROUP BY category 
     ORDER BY total DESC");

// Recent Transactions (last 10)
$recent_query = "
    (SELECT id, amount, description, category, date, 'income' as type FROM incomes ORDER BY date DESC LIMIT 5)
    UNION
    (SELECT id, amount, description, category, date, 'expense' as type FROM expenses ORDER BY date DESC LIMIT 5)
    ORDER BY date DESC LIMIT 10
";
$recent_result = mysqli_query($conn, $recent_query);

// Monthly Data for Chart (last 6 months)
$monthly_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M', strtotime($month . '-01'));
    
    // Get income for this month
    $month_income_query = mysqli_query($conn, 
        "SELECT SUM(amount) as total FROM incomes 
         WHERE DATE_FORMAT(date, '%Y-%m') = '$month'");
    $month_income_row = mysqli_fetch_assoc($month_income_query);
    $month_income_val = $month_income_row['total'] ? floatval($month_income_row['total']) : 0;
    
    // Get expense for this month
    $month_expense_query = mysqli_query($conn, 
        "SELECT SUM(amount) as total FROM expenses 
         WHERE DATE_FORMAT(date, '%Y-%m') = '$month'");
    $month_expense_row = mysqli_fetch_assoc($month_expense_query);
    $month_expense_val = $month_expense_row['total'] ? floatval($month_expense_row['total']) : 0;
    
    $monthly_data[] = [
        'month' => $month_name,
        'income' => $month_income_val,
        'expense' => $month_expense_val
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .stat-card { transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-3px); }
        .progress-bar { transition: width 1s ease-in-out; }
        .chart-container { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="bg-indigo-600 p-2 rounded-lg">
                        <i class="fas fa-chart-bar text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">MyBudget Dashboard</h1>
                        <p class="text-xs text-gray-500">Financial Analytics</p>
                    </div>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex space-x-6">
                    <a href="index.php" class="text-gray-600 hover:text-indigo-600">Home</a>
                    <a href="income.php" class="text-gray-600 hover:text-indigo-600">Incomes</a>
                    <a href="expenses.php" class="text-gray-600 hover:text-indigo-600">Expenses</a>
                    <a href="dashboard.php" class="text-indigo-600 font-semibold">Dashboard</a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="menuBtn" class="md:hidden text-gray-600">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobileMenu" class="md:hidden bg-white border-t hidden">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="index.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Home</a>
                    <a href="income.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Incomes</a>
                    <a href="expenses.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Expenses</a>
                    <a href="dashboard.php" class="block px-3 py-2 rounded-md bg-indigo-50 text-indigo-600">Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Financial Dashboard</h1>
            <p class="text-gray-600">Comprehensive overview of your financial health</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Income -->
            <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Total Income</h3>
                    <div class="bg-green-50 p-2 rounded-full">
                        <i class="fas fa-money-bill-wave text-green-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800 mb-2">$<?php echo number_format($total_income, 2); ?></p>
                <div class="flex items-center text-green-600 text-sm">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>All time income</span>
                </div>
            </div>
            
            <!-- Total Expenses -->
            <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Total Expenses</h3>
                    <div class="bg-red-50 p-2 rounded-full">
                        <i class="fas fa-shopping-cart text-red-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800 mb-2">$<?php echo number_format($total_expense, 2); ?></p>
                <div class="flex items-center text-red-600 text-sm">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>All time expenses</span>
                </div>
            </div>
            
            <!-- Current Balance -->
            <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Current Balance</h3>
                    <div class="bg-blue-50 p-2 rounded-full">
                        <i class="fas fa-wallet text-blue-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold <?php echo $balance >= 0 ? 'text-green-600' : 'text-red-600'; ?> mb-2">
                    $<?php echo number_format($balance, 2); ?>
                </p>
                <div class="flex items-center <?php echo $balance >= 0 ? 'text-green-600' : 'text-red-600'; ?> text-sm">
                    <i class="fas <?php echo $balance >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?> mr-1"></i>
                    <span><?php echo $balance >= 0 ? 'Positive balance' : 'Negative balance'; ?></span>
                </div>
            </div>
            
            <!-- Savings Rate -->
            <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Savings Rate</h3>
                    <div class="bg-purple-50 p-2 rounded-full">
                        <i class="fas fa-percentage text-purple-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800 mb-2">
                    <?php 
                    if ($total_income > 0) {
                        $savings_rate = (($total_income - $total_expense) / $total_income) * 100;
                        echo number_format($savings_rate, 1) . '%';
                    } else {
                        echo '0%';
                    }
                    ?>
                </p>
                <div class="flex items-center text-purple-600 text-sm">
                    <i class="fas fa-chart-line mr-1"></i>
                    <span>Of income saved</span>
                </div>
            </div>
        </div>

        <!-- Monthly Comparison -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- This Month -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">This Month (<?php echo date('F Y'); ?>)</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-green-600">Income</span>
                            <span class="text-sm font-medium text-green-600">$<?php echo number_format($month_income, 2); ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" 
                                 style="width: <?php echo $month_income > 0 ? '100%' : '0%'; ?>"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-red-600">Expenses</span>
                            <span class="text-sm font-medium text-red-600">$<?php echo number_format($month_expense, 2); ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full" 
                                 style="width: <?php echo $month_income > 0 ? min(100, ($month_expense / $month_income) * 100) : '0%'; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">Monthly Balance</span>
                            <span class="text-lg font-bold <?php echo $month_balance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                $<?php echo number_format($month_balance, 2); ?>
                            </span>
                        </div>
                        <?php if ($last_month_income > 0): ?>
                        <p class="text-sm text-gray-500 mt-2">
                            <?php 
                            $income_change = $month_income - $last_month_income;
                            $change_percent = $last_month_income > 0 ? ($income_change / $last_month_income) * 100 : 0;
                            ?>
                            <i class="fas <?php echo $income_change >= 0 ? 'fa-arrow-up text-green-500' : 'fa-arrow-down text-red-500'; ?> mr-1"></i>
                            Income <?php echo $income_change >= 0 ? 'up' : 'down'; ?> 
                            <?php echo number_format(abs($change_percent), 1); ?>% from last month
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Expense Categories -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Expense Categories</h3>
                <div class="space-y-3">
                    <?php 
                    if ($categories_result && mysqli_num_rows($categories_result) > 0):
                        while($category = mysqli_fetch_assoc($categories_result)):
                            $percentage = $total_expense > 0 ? ($category['total'] / $total_expense) * 100 : 0;
                    ?>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($category['category']); ?></span>
                            <span class="text-sm font-medium text-gray-700">$<?php echo number_format($category['total'], 2); ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-500 h-2 rounded-full progress-bar" 
                                 style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500"><?php echo number_format($percentage, 1); ?>% of total expenses</span>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-pie text-3xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500">No expense data available</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Income vs Expenses Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Income vs Expenses (Last 6 Months)</h3>
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
            
            <!-- Expense Distribution Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Expense Distribution</h3>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Recent Transactions</h3>
                <div class="flex space-x-2">
                    <a href="addIncome.php" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-lg text-white text-sm">
                        <i class="fas fa-plus mr-1"></i> Add Income
                    </a>
                    <a href="addExpences.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-white text-sm">
                        <i class="fas fa-plus mr-1"></i> Add Expense
                    </a>
                </div>
            </div>
            
            <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while($transaction = mysqli_fetch_assoc($recent_result)): 
                            $is_income = $transaction['type'] === 'income';
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $is_income ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <i class="fas <?php echo $is_income ? 'fa-arrow-down' : 'fa-arrow-up'; ?> mr-1"></i>
                                    <?php echo ucfirst($transaction['type']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800"><?php echo htmlspecialchars($transaction['description']); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100">
                                    <?php echo htmlspecialchars($transaction['category']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?php echo date('M d, Y', strtotime($transaction['date'])); ?></td>
                            <td class="px-4 py-3 text-sm font-semibold <?php echo $is_income ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $is_income ? '+' : '-'; ?>$<?php echo number_format($transaction['amount'], 2); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-receipt text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 mb-4">No transactions yet. Start by adding your first income or expense!</p>
                <div class="flex justify-center space-x-4">
                    <a href="addIncome.php" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-lg text-white">
                        <i class="fas fa-plus mr-2"></i>Add Income
                    </a>
                    <a href="addExpences.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-white">
                        <i class="fas fa-plus mr-2"></i>Add Expense
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Tips -->
        <div class="mt-8 bg-blue-50 border border-blue-100 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                <i class="fas fa-lightbulb mr-2"></i> Financial Tips
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg">
                    <h4 class="font-medium text-gray-800 mb-2">💡 Budget Rule</h4>
                    <p class="text-sm text-gray-600">Try the 50/30/20 rule: 50% needs, 30% wants, 20% savings.</p>
                </div>
                <div class="bg-white p-4 rounded-lg">
                    <h4 class="font-medium text-gray-800 mb-2">📊 Track Regularly</h4>
                    <p class="text-sm text-gray-600">Review your spending weekly to stay on track with goals.</p>
                </div>
                <div class="bg-white p-4 rounded-lg">
                    <h4 class="font-medium text-gray-800 mb-2">🎯 Set Goals</h4>
                    <p class="text-sm text-gray-600">Define clear financial goals for better motivation.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h3 class="text-xl font-bold mb-2">MyBudget Dashboard</h3>
                <p class="text-gray-300 mb-4">Comprehensive financial analytics for better money management</p>
                <p class="text-gray-400 text-sm">© <?php echo date('Y'); ?> MyBudget. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        document.getElementById('menuBtn').addEventListener('click', function() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobileMenu');
            const btn = document.getElementById('menuBtn');
            if (!menu.contains(event.target) && !btn.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });

        // Monthly Income vs Expenses Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($monthly_data, 'month')); ?>,
                datasets: [
                    {
                        label: 'Income',
                        data: <?php echo json_encode(array_column($monthly_data, 'income')); ?>,
                        backgroundColor: '#10B981',
                        borderRadius: 6,
                        barPercentage: 0.6
                    },
                    {
                        label: 'Expenses',
                        data: <?php echo json_encode(array_column($monthly_data, 'expense')); ?>,
                        backgroundColor: '#EF4444',
                        borderRadius: 6,
                        barPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });

        <?php
        mysqli_data_seek($categories_result, 0);
        $category_labels = [];
        $category_data = [];
        $category_colors = ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#6B7280', '#EC4899', '#14B8A6'];
        
        if ($categories_result && mysqli_num_rows($categories_result) > 0) {
            $i = 0;
            while($category = mysqli_fetch_assoc($categories_result)) {
                $category_labels[] = $category['category'];
                $category_data[] = $category['total'];
                $i++;
            }
        }
        ?>
        
        <?php if (!empty($category_labels)): ?>
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($category_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($category_data); ?>,
                    backgroundColor: <?php echo json_encode(array_slice($category_colors, 0, count($category_labels))); ?>,
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': $';
                                }
                                label += context.parsed.toFixed(2);
                                return label;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
        <?php else: ?>
        document.getElementById('categoryChart').innerHTML = 
            '<div class="h-full flex items-center justify-center text-gray-500">No expense data available for chart</div>';
        <?php endif; ?>
    </script>
</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>