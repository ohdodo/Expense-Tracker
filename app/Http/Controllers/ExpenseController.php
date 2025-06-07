<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    // Show all expenses for the logged-in user
    public function index(Request $request)
    {
        $query = Expense::where('user_id', Auth::id());

        if ($request->has('sort_by')) {
            $sort = $request->get('sort_by');
            if (in_array($sort, ['date', 'amount', 'category'])) {
                $query->orderBy($sort, 'asc');
            }
        }

        $expenses = $query->latest()->get();

        return view('home', compact('expenses'));
    }

    // Show the form to create a new expense
    public function create()
    {
        return view('expenses.create');
    }

    // Store a new expense
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
        ]);

        Expense::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'category' => $request->category,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
    }

    // Show the form to edit an existing expense
    public function edit($id)
    {
        $expense = Expense::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        return view('expenses.edit', compact('expense'));
    }

    // Update an existing expense
    public function update(Request $request, $id)
    {
        $expense = Expense::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $expense->update([
            'amount' => $request->amount,
            'category' => $request->category,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    // Delete an expense
    public function destroy($id)
    {
        $expense = Expense::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
