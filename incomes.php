<?php
require_once 'includes/db.php';
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
                    <a href="expences.php" class="text-gray-600 hover:text-indigo-600">Expenses</a>
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
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Income Management</h1>
            
            <?php
            $incomes = mysqli_query($conn, "SELECT * FROM incomes ORDER BY date DESC");
            if (mysqli_num_rows($incomes) > 0):
            ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left">Description</th>
                            <th class="px-6 py-3 text-left">Category</th>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Amount</th>
                            <th class="px-6 py-3 text-left">Edit</th>
                            <th class="px-6 py-3 text-left">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($incomes)): ?>
                        <tr class="border-t">
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['description']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['category']); ?></td>
                            <td class="px-6 py-4"><?php echo $row['date']; ?></td>
                            <td class="px-6 py-4 text-green-600 font-bold">
                                +$<?php echo number_format($row['amount'], 2); ?>
                            </td>
                            <td class="px-6 py-4"><i class="fa-regular fa-pen-to-square"></i></td>
                            <td class="px-6 py-4"><i class="fa-solid fa-trash"></i></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <p class="text-gray-500">No income records found.</p>
            </div>
            <?php endif; ?>
            
            <div class="mt-6">
                <a href="index.php" class="text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Home
                </a>
            </div>
        </div>
        <div class="w-full h-auto flex justify-center gap-8">
                <button onclick="openIncomeModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition cursor-pointer">
                        <i class="fas fa-plus mr-2"></i>Add Income
                    </button>
        </div>
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
        
    <script src="main.js"></script>

    </body>
</html>