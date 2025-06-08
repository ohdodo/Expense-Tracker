<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',        // Make sure this is included
        'amount',
        'period',      // Add this too
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the budget.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the budget.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope to get only active budgets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get current budgets (within date range)
     */
    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }

    /**
     * Get the amount spent in this budget
     */
    public function getSpentAmount()
    {
        $query = \App\Models\Expense::where('user_id', $this->user_id)
            ->whereBetween('date', [$this->start_date, $this->end_date]);
    
        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }
    
        return $query->sum('amount') ?: 0;
    }

    /**
     * Get the remaining amount in this budget
     */
    public function getRemainingAmount()
    {
        return $this->amount - $this->getSpentAmount();
    }

    /**
     * Get the usage percentage of this budget
     */
    public function getUsagePercentage()
    {
        if ($this->amount <= 0) {
            return 0;
        }
    
        return ($this->getSpentAmount() / $this->amount) * 100;
    }

    /**
     * Check if budget is exceeded
     */
    public function isExceeded()
    {
        return $this->getSpentAmount() > $this->amount;
    }

    /**
     * Check if budget is close to limit (80% or more)
     */
    public function isNearLimit()
    {
        return $this->getUsagePercentage() >= 80;
    }

    /**
     * Get formatted budget period
     */
    public function getFormattedPeriodAttribute()
    {
        return ucfirst($this->period);
    }

    /**
     * Get budget status
     */
    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        $percentage = $this->getUsagePercentage();
        
        if ($percentage >= 100) {
            return 'exceeded';
        } elseif ($percentage >= 80) {
            return 'warning';
        } else {
            return 'good';
        }
    }
}
