<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    // Notification types constants
    const TYPE_BUDGET_WARNING = 'budget_warning';
    const TYPE_BUDGET_EXCEEDED = 'budget_exceeded';
    const TYPE_EXPENSE_ADDED = 'expense_added';
    const TYPE_BUDGET_CREATED = 'budget_created';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    /**
     * Get the user that owns the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get only read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null
        ]);
    }

    /**
     * Create a budget warning notification
     */
    public static function createBudgetWarning($userId, $budget, $percentage)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BUDGET_WARNING,
            'title' => 'Budget Warning',
            'message' => "You've used " . number_format($percentage, 1) . "% of your '{$budget->name}' budget.",
            'data' => ['budget_id' => $budget->id, 'percentage' => $percentage],
            'is_read' => false,
        ]);
    }

    /**
     * Create a budget exceeded notification
     */
    public static function createBudgetExceeded($userId, $budget, $overAmount)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BUDGET_EXCEEDED,
            'title' => 'Budget Exceeded',
            'message' => "You've exceeded your '{$budget->name}' budget by $" . number_format($overAmount, 2) . ".",
            'data' => ['budget_id' => $budget->id, 'over_amount' => $overAmount],
            'is_read' => false,
        ]);
    }

    /**
     * Create an expense added notification
     */
    public static function createExpenseAdded($userId, $expense)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_EXPENSE_ADDED,
            'title' => 'Expense Added',
            'message' => "New expense of $" . number_format($expense->amount, 2) . " added to " . ($expense->category ? $expense->category->name : 'Uncategorized'),
            'data' => ['expense_id' => $expense->id],
            'is_read' => false,
        ]);
    }

    /**
     * Create a budget created notification
     */
    public static function createBudgetCreated($userId, $budget)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_BUDGET_CREATED,
            'title' => 'Budget Created',
            'message' => "New budget '{$budget->name}' created with limit of $" . number_format($budget->amount, 2),
            'data' => ['budget_id' => $budget->id],
            'is_read' => false,
        ]);
    }

    /**
     * Get notification icon based on type
     */
    public function getIconAttribute()
    {
        switch ($this->type) {
            case self::TYPE_BUDGET_WARNING:
                return '⚠️';
            case self::TYPE_BUDGET_EXCEEDED:
                return '🚨';
            case self::TYPE_EXPENSE_ADDED:
                return '💰';
            case self::TYPE_BUDGET_CREATED:
                return '📊';
            default:
                return '📢';
        }
    }

    /**
     * Get notification color based on type
     */
    public function getColorAttribute()
    {
        switch ($this->type) {
            case self::TYPE_BUDGET_WARNING:
                return 'yellow';
            case self::TYPE_BUDGET_EXCEEDED:
                return 'red';
            case self::TYPE_EXPENSE_ADDED:
                return 'blue';
            case self::TYPE_BUDGET_CREATED:
                return 'green';
            default:
                return 'gray';
        }
    }
}
