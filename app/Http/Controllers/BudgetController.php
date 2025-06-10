<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\Category;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function index()
    {
        // Check authentication
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        $budgets = Budget::where('user_id', $currentUser->id)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = Category::forUser($currentUser->id)->get();

        return view('budgets.index', compact('budgets', 'categories', 'currentUser'));
    }

    public function store(Request $request)
    {
        // Check authentication
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();

        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'period' => 'required|in:monthly,yearly',
        ]);

        // Calculate start and end dates based on period
        $startDate = Carbon::now()->startOfMonth();
        $endDate = $request->period === 'monthly' 
            ? Carbon::now()->endOfMonth()
            : Carbon::now()->endOfYear();

        $budget = Budget::create([
            'user_id' => $currentUser->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'period' => $request->period,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Create notification for new budget
        \App\Models\Notification::createBudgetCreated($currentUser->id, $budget);

        return redirect()->route('budgets.index')->with('success', 'Budget created successfully!');
    }

    public function update(Request $request, Budget $budget)
    {
        // Check authentication and ownership
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if ($budget->user_id !== $currentUser->id) {
            return redirect()->route('budgets.index')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        $budget->update($request->only(['name', 'amount', 'category_id', 'is_active']));

        return redirect()->route('budgets.index')->with('success', 'Budget updated successfully!');
    }

    public function destroy(Budget $budget)
    {
        // Check authentication and ownership
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if ($budget->user_id !== $currentUser->id) {
            return redirect()->route('budgets.index')->with('error', 'Unauthorized access.');
        }

        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Budget deleted successfully!');
    }
}
