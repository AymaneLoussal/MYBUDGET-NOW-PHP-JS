<?php
// Start session
session_start();

// Include database connection
require_once 'includes/db.php';

// Set page title
$pageTitle = "MyBudget - Personal Finance Manager";

// Get totals from database
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

// Get recent transactions
$recent_query = "
    (SELECT id, amount, description, category, date, 'income' as type FROM incomes ORDER BY date DESC LIMIT 5)
    UNION
    (SELECT id, amount, description, category, date, 'expense' as type FROM expenses ORDER BY date DESC LIMIT 5)
    ORDER BY date DESC LIMIT 10
";
$recent_result = mysqli_query($conn, $recent_query);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_income'])) {
        $amount = mysqli_real_escape_string($conn, $_POST['amount']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $date = mysqli_real_escape_string($conn, $_POST['date']);
        
        $sql = "INSERT INTO incomes (amount, description, category, date) 
                VALUES ('$amount', '$description', '$category', '$date')";
        
        if (mysqli_query($conn, $sql)) {
            echo '<script>alert("Income added successfully!"); window.location.href = "index.php";</script>';
        }
    }
    
    if (isset($_POST['add_expense'])) {
        $amount = mysqli_real_escape_string($conn, $_POST['amount']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $date = mysqli_real_escape_string($conn, $_POST['date']);
        
        $sql = "INSERT INTO expenses (amount, description, category, date) 
                VALUES ('$amount', '$description', '$category', '$date')";
        
        if (mysqli_query($conn, $sql)) {
            echo '<script>alert("Expense added successfully!"); window.location.href = "index.php";</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>my budget now</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Modal animations */
        .modal-enter {
            opacity: 0;
            transform: scale(0.95);
        }
        .modal-enter-active {
            opacity: 1;
            transform: scale(1);
            transition: opacity 200ms ease-out, transform 200ms ease-out;
        }
        .modal-exit {
            opacity: 1;
            transform: scale(1);
        }
        .modal-exit-active {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 200ms ease-in, transform 200ms ease-in;
        }
        
        /* Overlay */
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <header>
        <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="bg-indigo-600 p-2 rounded-lg">
                        <i class="fas fa-wallet text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">MyBudget</h1>
                        <p class="text-xs text-gray-500">Personal Finance Manager</p>
                    </div>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex space-x-6">
                    <a href="index.php" class="text-indigo-600 font-semibold">Home</a>
                    <a href="incomes.php" class="text-gray-600 hover:text-indigo-600">Incomes</a>
                    <a href="expenses.php" class="text-gray-600 hover:text-indigo-600">Expenses</a>
                    <a href="dashboard.php" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="menuBtn" class="md:hidden text-gray-600">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobileMenu" class="md:hidden bg-white border-t hidden">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="index.php" class="block px-3 py-2 rounded-md bg-indigo-50 text-indigo-600">Home</a>
                    <a href="incomes.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Incomes</a>
                    <a href="expenses.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Expenses</a>
                    <a href="dashboard.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

        <div class="relative isolate overflow-hidden bg-gray-900 py-24 mt-8 sm:py-32">
            <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&crop=focalpoint&fp-y=.8&w=2830&h=1500&q=80&blend=111827&sat=-100&exp=15&blend-mode=multiply" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover object-right md:object-center" />
            
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:mx-0">
                    <h2 class="text-5xl font-semibold tracking-tight text-white sm:text-7xl">Welcome to MyBudget Now</h2>
                    <p class="mt-8 text-lg font-medium text-pretty text-gray-300 sm:text-xl/8">Track your income, manage expenses, and achieve your financial goals with our simple yet powerful budgeting tool.</p>
                </div>
                <div class="flex flex-wrap gap-4 mt-8">
                    <!-- Buttons to open modals -->
                    <button onclick="openIncomeModal()" class="bg-transparent border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition cursor-pointer">
                        <i class="fas fa-plus-circle mr-2"></i>Add Income
                    </button>
                    <button onclick="openExpenseModal()" class="bg-transparent border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition cursor-pointer">
                        <i class="fas fa-minus-circle mr-2"></i>Add Expense
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Income Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-black hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Total Income</h3>
                    <div class="bg-green-50 p-2 rounded-full">
                        <i class="fas fa-money-bill-wave text-green-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800 mb-2">$<?php echo number_format($total_income, 2); ?></p>
                <p class="text-sm text-gray-500">All time income</p>
            </div>
            
            <!-- Total Expense Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-black hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Total Expenses</h3>
                    <div class="bg-red-50 p-2 rounded-full">
                        <i class="fas fa-shopping-cart text-red-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800 mb-2">$<?php echo number_format($total_expense, 2); ?></p>
                <p class="text-sm text-gray-500">All time expenses</p>
            </div>
            
            <!-- Balance Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-black hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Current Balance</h3>
                    <div class="bg-blue-50 p-2 rounded-full">
                        <i class="fas fa-wallet text-blue-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold <?php echo $balance >= 0 ? 'text-green-600' : 'text-red-600'; ?> mb-2">
                    $<?php echo number_format($balance, 2); ?>
                </p>
                <p class="text-sm text-gray-500">Income - Expenses</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <button onclick="openIncomeModal()" class="bg-white p-4 rounded-lg border border-gray-200 hover:border-indigo-300 hover:shadow-md transition text-center cursor-pointer group">
                <div class="text-indigo-600 mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-plus-circle text-2xl"></i>
                </div>
                <h4 class="font-medium text-gray-800">Add Income</h4>
            </button>
            
            <button onclick="openExpenseModal()" class="bg-white p-4 rounded-lg border border-gray-200 hover:border-red-300 hover:shadow-md transition text-center cursor-pointer group">
                <div class="text-red-600 mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-minus-circle text-2xl"></i>
                </div>
                <h4 class="font-medium text-gray-800">Add Expense</h4>
            </button>
            
            <a href="income.php" class="bg-white p-4 rounded-lg border border-gray-200 hover:border-green-300 hover:shadow-md transition text-center group">
                <div class="text-green-600 mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-list text-2xl"></i>
                </div>
                <h4 class="font-medium text-gray-800">View Incomes</h4>
            </a>
            
            <a href="expenses.php" class="bg-white p-4 rounded-lg border border-gray-200 hover:border-purple-300 hover:shadow-md transition text-center group">
                <div class="text-purple-600 mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-chart-bar text-2xl"></i>
                </div>
                <h4 class="font-medium text-gray-800">View Expenses</h4>
            </a>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mr-2">Recent Transactions</h2>
                <div class="flex space-x-2">
                    <button onclick="openIncomeModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition cursor-pointer">
                        <i class="fas fa-plus mr-2"></i>Add Income
                    </button>
                    <button onclick="openExpenseModal()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition cursor-pointer">
                        <i class="fas fa-plus mr-2"></i>Add Expense
                    </button>
                </div>
            </div>
            
            <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
            <div class="space-y-4">
                <?php while($transaction = mysqli_fetch_assoc($recent_result)): 
                    $is_income = $transaction['type'] === 'income';
                ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 <?php echo $is_income ? 'bg-green-100' : 'bg-red-100'; ?>">
                            <i class="<?php echo $is_income ? 'fas fa-arrow-down text-green-600' : 'fas fa-arrow-up text-red-600'; ?>"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-800"><?php echo htmlspecialchars($transaction['description']); ?></h4>
                            <p class="text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($transaction['date'])); ?> • 
                                <?php echo htmlspecialchars($transaction['category']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold <?php echo $is_income ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $is_income ? '+' : '-'; ?>$<?php echo number_format($transaction['amount'], 2); ?>
                        </p>
                        <span class="inline-block px-2 py-1 text-xs rounded-full <?php echo $is_income ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo ucfirst($transaction['type']); ?>
                        </span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-receipt text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 mb-4">No transactions yet. Add your first income or expense!</p>
                <div class="flex justify-center space-x-4">
                    <button onclick="openIncomeModal()" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-lg text-white font-medium transition cursor-pointer">
                        <i class="fas fa-plus mr-2"></i>Add Income
                    </button>
                    <button onclick="openExpenseModal()" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-white font-medium transition cursor-pointer">
                        <i class="fas fa-plus mr-2"></i>Add Expense
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h3 class="text-xl font-bold mb-2">MyBudget Now</h3>
                <p class="text-gray-300 mb-4">Personal Finance Manager</p>
                <p class="text-gray-400 text-sm">© <?php echo date('Y'); ?> MyBudget. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Income Modal -->
    <div id="incomeModal" class="fixed inset-0 z-50 hidden">
        <div class="modal-overlay absolute inset-0"></div>
        
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all w-full max-w-md">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-plus-circle mr-2"></i>Add New Income
                        </h3>
                        <button onclick="closeIncomeModal()" class="text-white hover:text-gray-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Form -->
                <form method="POST" class="p-6">
                    <input type="hidden" name="add_income" value="1">
                    
                    <!-- Amount -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Amount ($) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">$</span>
                            </div>
                            <input type="number" 
                                   name="amount" 
                                   step="0.01"
                                   min="0"
                                   required
                                   class="pl-8 pr-4 py-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="description" 
                               required
                               class="px-4 py-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Salary, Bonus, Freelance...">
                    </div>
                    
                    <!-- Category -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Category
                        </label>
                        <select name="category" 
                                class="px-4 py-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="Salary">Salary</option>
                            <option value="Freelance">Freelance</option>
                            <option value="Investment">Investment</option>
                            <option value="Bonus">Bonus</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <!-- Date -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="date" 
                               value="<?php echo date('Y-m-d'); ?>"
                               required
                               class="px-4 py-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" 
                                onclick="closeIncomeModal()"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-medium hover:from-green-600 hover:to-emerald-700 transition">
                            <i class="fas fa-save mr-2"></i>Save Income
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Expense Modal -->
    <div id="expenseModal" class="fixed inset-0 z-50 hidden">
        <div class="modal-overlay absolute inset-0"></div>
        
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all w-full max-w-md">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-red-500 to-pink-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-minus-circle mr-2"></i>Add New Expense
                        </h3>
                        <button onclick="closeExpenseModal()" class="text-white hover:text-gray-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <form method="POST" class="p-6">
                    <input type="hidden" name="add_expense" value="1">
                    
                    <!-- Amount -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Amount ($) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">$</span>
                            </div>
                            <input type="number" 
                                   name="amount" 
                                   step="0.01"
                                   min="0"
                                   required
                                   class="pl-8 pr-4 py-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="description" 
                               required
                               class="px-4 py-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="Groceries, Rent, Gas...">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Category
                        </label>
                        <select name="category" 
                                class="px-4 py-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            <option value="Food">Food</option>
                            <option value="Transport">Transport</option>
                            <option value="Shopping">Shopping</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Bills">Bills</option>
                            <option value="Housing">Housing</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="date" 
                               value="<?php echo date('Y-m-d'); ?>"
                               required
                               class="px-4 py-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" 
                                onclick="closeExpenseModal()"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-lg font-medium hover:from-red-600 hover:to-pink-700 transition">
                            <i class="fas fa-save mr-2"></i>Save Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="main.js"></script>
</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>