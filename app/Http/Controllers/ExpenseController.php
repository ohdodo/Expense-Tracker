<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Budget;
use App\Models\Notification;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        // Check authentication using AuthController
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }
        
        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser || !$currentUser->id) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $query = Expense::where('user_id', $currentUser->id)->with('category');

        // Apply sorting
        if ($request->has('sort_by') && $request->sort_by) {
            $sortBy = $request->sort_by;
            if (in_array($sortBy, ['date', 'amount'])) {
                $query->orderBy($sortBy, $sortBy === 'amount' ? 'desc' : 'asc');
            }
        } else {
            $query->orderBy('date', 'desc');
        }

        // Apply category filter
        if ($request->has('filter_category') && $request->filter_category) {
            $query->where('category_id', $request->filter_category);
        }

        // Apply date range filter if provided
        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        // Get paginated expenses
        $expenses = $query->paginate(10);

        // Get categories for the current user
        $categories = Category::forUser($currentUser->id)->get();

        // Get current budgets
        $budgets = Budget::where('user_id', $currentUser->id)
            ->active()
            ->current()
            ->with('category')
            ->get();

        // Get recent notifications
        $notifications = Notification::where('user_id', $currentUser->id)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Prepare data for category pie chart - using category relationship with proper table prefixes
        $categoryData = Expense::where('expenses.user_id', $currentUser->id)
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, SUM(expenses.amount) as total')
            ->groupBy('categories.id', 'categories.name')
            ->pluck('total', 'category_name')
            ->toArray();

        // Prepare data for monthly line chart (last 12 months)
        $monthlyData = [];
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        
        for ($i = 0; $i < 12; $i++) {
            $currentMonth = $startDate->copy()->addMonths($i);
            $monthLabel = $currentMonth->format('M Y');
            
            $total = Expense::where('user_id', $currentUser->id)
                ->whereYear('date', $currentMonth->year)
                ->whereMonth('date', $currentMonth->month)
                ->sum('amount');
                
            $monthlyData[$monthLabel] = (float) $total;
        }

        // Get all expenses for calculations
        $allExpenses = Expense::where('user_id', $currentUser->id)->get();

        return view('home', compact(
            'expenses', 
            'categories', 
            'categoryData', 
            'monthlyData', 
            'allExpenses', 
            'currentUser',
            'budgets',
            'notifications'
        ));
    }

    public function store(Request $request)
    {
        // Check authentication using AuthController
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser || !$currentUser->id) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
        ]);

        // Verify the category belongs to the user or is a default category
        $category = Category::where('id', $request->category_id)
            ->where(function($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id)
                      ->orWhere('is_default', true);
            })
            ->first();

        if (!$category) {
            return redirect()->back()->with('error', 'Invalid category selected.');
        }

        $expense = Expense::create([
            'user_id' => $currentUser->id,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        // Check budget limits after adding expense
        $this->checkBudgetLimits($currentUser->id, $request->category_id, $request->amount);

        return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
    }

    public function edit($id)
    {
        // Check authentication using AuthController
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser || !$currentUser->id) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $expense = Expense::where('user_id', $currentUser->id)
            ->where('id', $id)
            ->with('category')
            ->first();

        if (!$expense) {
            return redirect()->route('expenses.index')->with('error', 'Expense not found.');
        }

        $categories = Category::forUser($currentUser->id)->get();

        return view('expenses.edit', compact('expense', 'categories', 'currentUser'));
    }

    public function update(Request $request, $id)
    {
        // Check authentication using AuthController
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser || !$currentUser->id) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $expense = Expense::where('user_id', $currentUser->id)
            ->where('id', $id)
            ->first();

        if (!$expense) {
            return redirect()->route('expenses.index')->with('error', 'Expense not found.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
        ]);

        // Verify the category belongs to the user or is a default category
        $category = Category::where('id', $request->category_id)
            ->where(function($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id)
                      ->orWhere('is_default', true);
            })
            ->first();

        if (!$category) {
            return redirect()->back()->with('error', 'Invalid category selected.');
        }

        $oldAmount = $expense->amount;
        $oldCategoryId = $expense->category_id;

        $expense->update([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        // Check budget limits if amount or category changed
        if ($oldAmount != $request->amount || $oldCategoryId != $request->category_id) {
            $this->checkBudgetLimits($currentUser->id, $request->category_id);
        }

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy($id)
    {
        // Check authentication using AuthController
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser || !$currentUser->id) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $expense = Expense::where('user_id', $currentUser->id)
            ->where('id', $id)
            ->first();

        if (!$expense) {
            return redirect()->route('expenses.index')->with('error', 'Expense not found.');
        }

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }

    /**
     * Check budget limits and create notifications if necessary
     */
    private function checkBudgetLimits($userId, $categoryId, $newExpenseAmount = 0)
    {
        // Get active budgets for this user
        $budgets = Budget::where('user_id', $userId)
            ->active()
            ->current()
            ->where(function($query) use ($categoryId) {
                $query->whereNull('category_id') // Overall budgets
                      ->orWhere('category_id', $categoryId); // Category-specific budgets
            })
            ->get();

        foreach ($budgets as $budget) {
            $spentAmount = $budget->getSpentAmount();
            $percentage = $budget->amount > 0 ? ($spentAmount / $budget->amount) * 100 : 0;

            // Check for budget warnings (80% threshold)
            if ($percentage >= 80 && $percentage < 100) {
                $existingNotification = Notification::where('user_id', $userId)
                    ->where('type', Notification::TYPE_BUDGET_WARNING)
                    ->whereJsonContains('data->budget_id', $budget->id)
                    ->where('created_at', '>=', $budget->start_date)
                    ->first();

                if (!$existingNotification) {
                    Notification::createBudgetWarning($userId, $budget, $percentage);
                }
            }

            // Check for budget exceeded (100% threshold)
            if ($percentage >= 100) {
                $existingNotification = Notification::where('user_id', $userId)
                    ->where('type', Notification::TYPE_BUDGET_EXCEEDED)
                    ->whereJsonContains('data->budget_id', $budget->id)
                    ->where('created_at', '>=', $budget->start_date)
                    ->first();

                if (!$existingNotification) {
                    $overAmount = $spentAmount - $budget->amount;
                    Notification::createBudgetExceeded($userId, $budget, $overAmount);
                }
            }
        }
    }

    /**
     * Export expenses to CSV
     */
    public function export(Request $request)
    {
        // Check authentication using AuthController
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser || !$currentUser->id) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $query = Expense::where('user_id', $currentUser->id)->with('category');

        // Apply filters if provided
        if ($request->has('filter_category') && $request->filter_category) {
            $query->where('category_id', $request->filter_category);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        $expenses = $query->orderBy('date', 'desc')->get();

        $filename = 'expenses_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($expenses, $currentUser) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, ['Date', 'Category', 'Description', 'Amount']);
            
            // Add data rows
            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->date->format('Y-m-d'),
                    $expense->category ? $expense->category->name : 'Uncategorized',
                    $expense->description ?: '',
                    number_format($expense->amount, 2)
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
