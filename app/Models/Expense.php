<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',  // Now using category_id instead of category string
        'amount',
        'date',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the expense
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that the expense belongs to
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get category name (for backward compatibility)
     */
    public function getCategoryName()
    {
        return $this->category ? $this->category->name : 'Uncategorized';
    }

    /**
     * Get category icon
     */
    public function getCategoryIcon()
    {
        return $this->category ? $this->category->icon : '📝';
    }

    /**
     * Get category color
     */
    public function getCategoryColor()
    {
        return $this->category ? $this->category->color : '#6B7280';
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope for current month expenses
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year);
    }

    /**
     * Scope for current year expenses
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('date', Carbon::now()->year);
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('M d, Y');
    }

    /**
     * Check if expense is from current month
     */
    public function isCurrentMonth()
    {
        return $this->date->isCurrentMonth();
    }

    /**
     * Check if expense is from current year
     */
    public function isCurrentYear()
    {
        return $this->date->isCurrentYear();
    }
}
