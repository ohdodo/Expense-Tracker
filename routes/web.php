<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Route to create storage link (temporary, remove in production)
Route::get('/create-storage-link', function () {
    try {
        \Artisan::call('storage:link');
        return 'Storage link created successfully!';
    } catch (\Exception $e) {
        return 'Error creating storage link: ' . $e->getMessage();
    }
});

// Test route for session debugging
Route::get('/test-session', function () {
    // Set a test value
    session(['test_value' => 'This is a test at ' . now()]);
    
    // Get current session data
    $sessionData = [
        'user_id' => session('user_id'),
        'user_email' => session('user_email'),
        'test_value' => session('test_value'),
        'session_id' => session()->getId(),
        'all' => session()->all()
    ];
    
    return response()->json($sessionData);
});

// Redirect root to expenses
Route::get('/', function () {
    if (\App\Models\User::isLoggedIn()) {
        return redirect()->route('expenses.index');
    }
    return redirect()->route('login');
});

// Authentication Routes (accessible only when not logged in)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Logout route (accessible when logged in)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (require manual authentication check)
Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
Route::get('/expenses/{id}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
Route::put('/expenses/{id}', [ExpenseController::class, 'update'])->name('expenses.update');
Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

// Budget Routes
Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
Route::put('/budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

// Notification Routes
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');

// Profile Routes (require manual authentication check)
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
Route::get('/profile/remove-picture', [ProfileController::class, 'removeProfilePicture'])->name('profile.remove-picture');

// Alternative route names for compatibility
Route::get('/home', [ExpenseController::class, 'index'])->name('home');
Route::get('/dashboard', [ExpenseController::class, 'index'])->name('dashboard');
