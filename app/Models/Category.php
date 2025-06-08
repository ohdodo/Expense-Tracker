<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'color',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the category
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get expenses that belong to this category
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get budgets that belong to this category
     */
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Scope to get categories available for a specific user
     * This includes both user-specific categories and default categories
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('is_default', true);
        })->orderBy('is_default', 'desc')
          ->orderBy('name', 'asc');
    }

    /**
     * Scope to get only default categories
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get only user-specific categories
     */
    public function scopeUserSpecific($query, $userId)
    {
        return $query->where('user_id', $userId)
                    ->where('is_default', false);
    }

    /**
     * Check if category is default
     */
    public function isDefault()
    {
        return $this->is_default;
    }

    /**
     * Check if category belongs to a specific user
     */
    public function belongsToUser($userId)
    {
        return $this->user_id == $userId;
    }

    /**
     * Get the category display name with icon
     */
    public function getDisplayNameAttribute()
    {
        return $this->icon . ' ' . $this->name;
    }

    /**
     * Get total expenses for this category for a specific user
     */
    public function getTotalExpensesForUser($userId, $startDate = null, $endDate = null)
    {
        $query = $this->expenses()->where('user_id', $userId);
        
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        
        return $query->sum('amount');
    }

    /**
     * Get expense count for this category for a specific user
     */
    public function getExpenseCountForUser($userId, $startDate = null, $endDate = null)
    {
        $query = $this->expenses()->where('user_id', $userId);
        
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        
        return $query->count();
    }

    /**
     * Create a new category for a user (copy from default if needed)
     */
    public static function createForUser($userId, $name, $icon = '📝', $color = '#6B7280')
    {
        return self::create([
            'user_id' => $userId,
            'name' => $name,
            'icon' => $icon,
            'color' => $color,
            'is_default' => false,
        ]);
    }

    /**
     * Get or create a category by name for a user
     */
    public static function getOrCreateForUser($userId, $name, $icon = '📝', $color = '#6B7280')
    {
        // First try to find existing category for this user
        $category = self::where('user_id', $userId)
                       ->where('name', $name)
                       ->first();

        if ($category) {
            return $category;
        }

        // Try to find a default category with this name
        $defaultCategory = self::where('is_default', true)
                              ->where('name', $name)
                              ->first();

        if ($defaultCategory) {
            return $defaultCategory;
        }

        // Create new category for user
        return self::createForUser($userId, $name, $icon, $color);
    }

    /**
     * Get categories with expense statistics for a user
     */
    public static function getWithStatsForUser($userId, $startDate = null, $endDate = null)
    {
        $categories = self::forUser($userId)->get();
        
        return $categories->map(function ($category) use ($userId, $startDate, $endDate) {
            $category->total_expenses = $category->getTotalExpensesForUser($userId, $startDate, $endDate);
            $category->expense_count = $category->getExpenseCountForUser($userId, $startDate, $endDate);
            return $category;
        });
    }
}
