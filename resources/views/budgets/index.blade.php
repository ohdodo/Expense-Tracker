<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Budget Management - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.x/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .logo-placeholder {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .budget-progress {
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }
        .budget-progress-bar {
            height: 100%;
            transition: width 0.3s ease;
        }
        .budget-warning { background-color: #f59e0b; }
        .budget-danger { background-color: #ef4444; }
        .budget-success { background-color: #10b981; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="gradient-bg shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="logo-container">
                    <div class="logo-placeholder">ET</div>
                    <h1 class="text-2xl font-bold text-white">Budget Management</h1>
                </div>
                <a href="{{ route('expenses.index') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition duration-200">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Add Budget Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create New Budget
            </h2>
            
            <form action="{{ route('budgets.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Budget Name</label>
                    <input type="text" name="name" id="name" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., Monthly Food Budget">
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Budget Amount</label>
                    <input type="number" step="0.01" name="amount" id="amount" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="0.00">
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category (Optional)</label>
                    <select name="category_id" id="category_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->icon }} {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                    <select name="period" id="period" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-4 py-2 rounded-lg transition duration-200 transform hover:scale-105">
                        Create Budget
                    </button>
                </div>
            </form>
        </div>

        <!-- Budgets List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 gradient-bg">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Your Budgets
                </h2>
            </div>

            @if($budgets->count() > 0)
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($budgets as $budget)
                            @php
                                $spent = $budget->getSpentAmount();
                                $remaining = $budget->getRemainingAmount();
                                $percentage = $budget->getUsagePercentage();
                                $progressClass = $percentage >= 100 ? 'budget-danger' : ($percentage >= 80 ? 'budget-warning' : 'budget-success');
                            @endphp
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow duration-200">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="font-semibold text-gray-800 text-lg">{{ $budget->name }}</h3>
                                        <p class="text-sm text-gray-500 capitalize">{{ $budget->period }} Budget</p>
                                    </div>
                                    @if($budget->category)
                                        <span class="text-sm px-3 py-1 rounded-full" style="background-color: {{ $budget->category->color }}20; color: {{ $budget->category->color }}">
                                            {{ $budget->category->icon }} {{ $budget->category->name }}
                                        </span>
                                    @else
                                        <span class="text-sm px-3 py-1 rounded-full bg-gray-100 text-gray-600">
                                            All Categories
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="mb-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                                        <span>{{ $currentUser->getCurrencySymbol() }}{{ number_format($spent, 2) }} spent</span>
                                        <span>{{ $currentUser->getCurrencySymbol() }}{{ number_format($budget->amount, 2) }} budget</span>
                                    </div>
                                    <div class="budget-progress">
                                        <div class="budget-progress-bar {{ $progressClass }}" style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 mt-2">
                                        <span>{{ number_format($percentage, 1) }}% used</span>
                                        <span class="{{ $remaining < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $remaining < 0 ? 'Over by ' : 'Remaining: ' }}{{ $currentUser->getCurrencySymbol() }}{{ number_format(abs($remaining), 2) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex justify-between text-xs text-gray-500 mb-4">
                                    <span>{{ $budget->start_date->format('M d') }} - {{ $budget->end_date->format('M d, Y') }}</span>
                                    <span class="{{ $budget->is_active ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $budget->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>

                                <div class="flex space-x-2">
                                    <button onclick="editBudget({{ $budget->id }}, '{{ $budget->name }}', {{ $budget->amount }}, {{ $budget->category_id ?? 'null' }}, {{ $budget->is_active ? 'true' : 'false' }})" 
                                        class="flex-1 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Edit
                                    </button>
                                    <form action="{{ route('budgets.destroy', $budget->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this budget?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-red-600 hover:text-red-800 text-sm font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No budgets found</h3>
                    <p class="text-gray-500">Create your first budget to start tracking your spending limits.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Budget Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Edit Budget</h3>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">Budget Name</label>
                        <input type="text" name="name" id="edit_name" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="edit_amount" class="block text-sm font-medium text-gray-700 mb-2">Budget Amount</label>
                        <input type="number" step="0.01" name="amount" id="edit_amount" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="edit_category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category_id" id="edit_category_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->icon }} {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition duration-200">
                        Update Budget
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editBudget(id, name, amount, categoryId, isActive) {
            document.getElementById('editForm').action = `/budgets/${id}`;
            document.getElementByI
            document.getElementById('editForm').action = `/budgets/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_amount').value = amount;
            document.getElementById('edit_category_id').value = categoryId || '';
            document.getElementById('edit_is_active').checked = isActive;
            
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>

</html>