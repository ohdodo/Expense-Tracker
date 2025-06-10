<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'currency',
        'monthly_budget',
        'profile_picture',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'monthly_budget' => 'decimal:2',
    ];

    /**
     * Get the expenses for the user.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the budgets for the user.
     */
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get the categories for the user.
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the currency symbol for the user
     */
    public function getCurrencySymbol()
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'PHP' => '₱',
        ];

        return $symbols[$this->currency ?? 'USD'] ?? '$';
    }

    /**
     * Get user initials for avatar
     */
    public function getInitials()
    {
        $names = explode(' ', trim($this->name));
        $initials = '';
        
        foreach ($names as $name) {
            if (!empty($name)) {
                $initials .= strtoupper(substr($name, 0, 1));
            }
        }
        
        return !empty($initials) ? substr($initials, 0, 2) : 'U';
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn()
    {
        return session()->has('user_id') && session()->has('user_email');
    }

    /**
     * Get current logged in user
     */
    public static function getCurrentUser()
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        return self::where('id', session('user_id'))
                  ->where('email', session('user_email'))
                  ->first();
    }

    /**
     * Log out the current user
     */
    public static function logout()
    {
        session()->forget(['user_id', 'user_email']);
        session()->regenerate();
        return true;
    }

    /**
     * Get monthly budget progress
     */
    public function getMonthlyBudgetProgress()
    {
        if (!$this->monthly_budget) {
            return null;
        }

        $currentMonthExpenses = $this->expenses()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        return [
            'budget' => $this->monthly_budget,
            'spent' => $currentMonthExpenses,
            'remaining' => $this->monthly_budget - $currentMonthExpenses,
            'percentage' => ($currentMonthExpenses / $this->monthly_budget) * 100
        ];
    }

    /**
     * Get profile picture URL
     */
    public function getProfilePictureUrl()
    {
        if ($this->profile_picture && file_exists(public_path('storage/' . $this->profile_picture))) {
            return asset('storage/' . $this->profile_picture);
        }
        
        return null;
    }

    /**
     * Check if user has a profile picture
     */
    public function hasProfilePicture()
    {
        return $this->profile_picture && file_exists(public_path('storage/' . $this->profile_picture));
    }

    /**
     * Get formatted name for display
     */
    public function getDisplayName()
    {
        return $this->name ?: 'User';
    }

    /**
     * Get total expenses for the user
     */
    public function getTotalExpenses()
    {
        return $this->expenses()->sum('amount');
    }

    /**
     * Get current month expenses for the user
     */
    public function getCurrentMonthExpenses()
    {
        return $this->expenses()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
    }

    /**
     * Get active budgets count
     */
    public function getActiveBudgetsCount()
    {
        return $this->budgets()
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCount()
    {
        return $this->notifications()
            ->where('is_read', false)
            ->count();
    }
}
