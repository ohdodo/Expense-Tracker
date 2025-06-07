<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.x/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-200 min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Expense Tracker</h1>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Logout</button>
            </form>
        </div>

        <div class="mb-4">
            <h2 class="text-lg">Welcome, {{ Auth::user()->name }}!</h2>
            <p class="text-gray-600">Track and manage your expenses below.</p>
        </div>

        <div class="mb-6 flex justify-between items-center">
            <button id="openModalBtn" class="bg-blue-500 text-white px-4 py-2 rounded">
                + Add Expense
            </button>

            <form method="GET" action="{{ route('expenses.index') }}" class="flex space-x-2">
                <select name="sort_by" class="border border-gray-300 rounded px-2 py-1">
                    <option value="">Sort by</option>
                    <option value="date">Date</option>
                    <option value="amount">Amount</option>
                    <option value="category">Category</option>
                </select>
                <button type="submit" class="bg-gray-300 px-3 py-1 rounded">Sort</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border">Date</th>
                        <th class="py-2 px-4 border">Category</th>
                        <th class="py-2 px-4 border">Amount</th>
                        <th class="py-2 px-4 border">Description</th>
                        <th class="py-2 px-4 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td class="py-2 px-4 border">{{ $expense->date }}</td>
                            <td class="py-2 px-4 border">{{ $expense->category }}</td>
                            <td class="py-2 px-4 border">₱{{ number_format($expense->amount, 2) }}</td>
                            <td class="py-2 px-4 border">{{ $expense->description ?? '-' }}</td>
                            <td class="py-2 px-4 border">
                                <a href="{{ route('expenses.edit', $expense->id) }}"
                                    class="text-blue-600 hover:underline mr-2">Edit</a>
                                <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST"
                                    class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline"
                                        onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">No expenses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- HERE -->

    @section('content')
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Expense Tracker</h1>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Logout</button>
                </form>
            </div>

            <div class="mb-4">
                <h2 class="text-lg">Welcome, {{ Auth::user()->name }}!</h2>
                <p class="text-gray-600">Track and manage your expenses below.</p>
            </div>

            <div class="mb-6 flex justify-between items-center">
                <!-- Change this link to a button that opens the modal -->
                <button id="openModalBtn" class="bg-blue-500 text-white px-4 py-2 rounded">
                    + Add Expense
                </button>

                <form method="GET" action="{{ route('expenses.index') }}" class="flex space-x-2">
                    <select name="sort_by" class="border border-gray-300 rounded px-2 py-1">
                        <option value="">Sort by</option>
                        <option value="date">Date</option>
                        <option value="amount">Amount</option>
                        <option value="category">Category</option>
                    </select>
                    <button type="submit" class="bg-gray-300 px-3 py-1 rounded">Sort</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 border">Date</th>
                            <th class="py-2 px-4 border">Category</th>
                            <th class="py-2 px-4 border">Amount</th>
                            <th class="py-2 px-4 border">Description</th>
                            <th class="py-2 px-4 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td class="py-2 px-4 border">{{ $expense->date }}</td>
                                <td class="py-2 px-4 border">{{ $expense->category }}</td>
                                <td class="py-2 px-4 border">₱{{ number_format($expense->amount, 2) }}</td>
                                <td class="py-2 px-4 border">{{ $expense->description ?? '-' }}</td>
                                <td class="py-2 px-4 border">
                                    <a href="{{ route('expenses.edit', $expense->id) }}"
                                        class="text-blue-600 hover:underline mr-2">Edit</a>
                                    <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST"
                                        class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline"
                                            onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500">No expenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Background -->
        <div id="modalBg" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
            <!-- Modal Content -->
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
                <button id="closeModalBtn"
                    class="absolute top-3 right-3 text-gray-700 text-2xl font-bold hover:text-gray-900">&times;</button>
                <h2 class="text-xl font-semibold mb-4">Add New Expense</h2>

                <form action="{{ route('expenses.store') }}" method="POST" id="expenseForm">
                    @csrf

                    <label for="amount" class="block font-medium">Amount</label>
                    <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" required
                        class="w-full border border-gray-300 rounded px-2 py-1 mb-2" />
                    @error('amount')<div class="text-red-600 text-sm mb-2">{{ $message }}</div>@enderror

                    <label for="category" class="block font-medium">Category</label>
                    <input type="text" name="category" id="category" value="{{ old('category') }}" required
                        class="w-full border border-gray-300 rounded px-2 py-1 mb-2" />
                    @error('category')<div class="text-red-600 text-sm mb-2">{{ $message }}</div>@enderror

                    <label for="date" class="block font-medium">Date</label>
                    <input type="date" name="date" id="date" value="{{ old('date') }}" required
                        class="w-full border border-gray-300 rounded px-2 py-1 mb-2" />
                    @error('date')<div class="text-red-600 text-sm mb-2">{{ $message }}</div>@enderror

                    <label for="description" class="block font-medium">Description (optional)</label>
                    <textarea name="description" id="description"
                        class="w-full border border-gray-300 rounded px-2 py-1 mb-4">{{ old('description') }}</textarea>
                    @error('description')<div class="text-red-600 text-sm mb-2">{{ $message }}</div>@enderror

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add
                            Expense</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Get modal elements
            const modalBg = document.getElementById('modalBg');
            const openModalBtn = document.getElementById('openModalBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');

            // Open modal
            openModalBtn.addEventListener('click', () => {
                modalBg.classList.remove('hidden');
            });

            // Close modal
            closeModalBtn.addEventListener('click', () => {
                modalBg.classList.add('hidden');
            });

            // Optional: Close modal if clicked outside content
            modalBg.addEventListener('click', (e) => {
                if (e.target === modalBg) {
                    modalBg.classList.add('hidden');
                }
            });
        </script>

    @endsection

    <!-- Modal -->
    <div id="modalBg" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4 relative">
            <button id="closeModalBtn"
                class="absolute top-2 right-2 text-gray-700 hover:text-gray-900 font-bold text-xl">&times;</button>
            <h2 class="text-xl font-semibold mb-4">Add Expense</h2>

            <form action="{{ route('expenses.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="amount" class="block font-medium mb-1">Amount</label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01"
                        class="w-full border rounded px-3 py-2" required>
                    @error('amount') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="category" class="block font-medium mb-1">Category</label>
                    <input type="text" name="category" id="category" value="{{ old('category') }}"
                        class="w-full border rounded px-3 py-2" required>
                    @error('category') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="date" class="block font-medium mb-1">Date</label>
                    <input type="date" name="date" id="date" value="{{ old('date') ?? date('Y-m-d') }}"
                        class="w-full border rounded px-3 py-2" required>
                    @error('date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block font-medium mb-1">Description (optional)</label>
                    <textarea name="description" id="description"
                        class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="text-right">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Expense</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modalBg = document.getElementById('modalBg');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');

        openModalBtn.addEventListener('click', () => {
            modalBg.classList.remove('hidden');
        });

        closeModalBtn.addEventListener('click', () => {
            modalBg.classList.add('hidden');
        });

        window.addEventListener('click', (e) => {
            if (e.target === modalBg) {
                modalBg.classList.add('hidden');
            }
        });

        // If validation errors exist, open modal automatically
        @if ($errors->any())
            modalBg.classList.remove('hidden');
        @endif
    </script>
</body>

</html>