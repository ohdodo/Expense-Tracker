<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    // Add these constants at the top of the class
    const TYPE_BUDGET_WARNING = 'budget_warning';
    const TYPE_BUDGET_EXCEEDED = 'budget_exceeded';
    const TYPE_PROFILE_UPDATED = 'profile_updated';
    const TYPE_EXPENSE_ADDED = 'expense_added';
    const TYPE_EXPENSE_UPDATED = 'expense_updated';
    const TYPE_EXPENSE_DELETED = 'expense_deleted';
    const TYPE_BUDGET_CREATED = 'budget_created';
    const TYPE_BUDGET_UPDATED = 'budget_updated';

    protected $fillable = [
        'user_id',  // This was missing!
        'type',
        'title',    // This was missing!
        'message',  // This was missing!
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
     * Get the decoded data attribute
     */
    public function getDataAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Set the data attribute
     */
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = is_array($value) ? json_encode($value) : $value;
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
            'data' => ['budget_id' => $budget->id],
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
            'data' => ['budget_id' => $budget->id],
            'is_read' => false,
        ]);
    }

    /**
     * Create a profile updated notification
     */
    public static function createProfileUpdated($userId, $changes = [])
    {
        $changeText = '';
        if (!empty($changes)) {
            $changeText = ' Changes: ' . implode(', ', $changes);
        }

        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_PROFILE_UPDATED,
            'title' => 'Profile Updated',
            'message' => 'Your profile has been successfully updated.' . $changeText,
            'data' => ['changes' => $changes],
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
            'message' => "New expense of $" . number_format($expense->amount, 2) . " added for " . ($expense->category ? $expense->category->name : 'Uncategorized'),
            'data' => ['expense_id' => $expense->id],
            'is_read' => false,
        ]);
    }

    /**
     * Create an expense updated notification
     */
    public static function createExpenseUpdated($userId, $expense, $oldAmount = null)
    {
        $message = "Expense updated";
        if ($oldAmount && $oldAmount != $expense->amount) {
            $message .= " - amount changed from $" . number_format($oldAmount, 2) . " to $" . number_format($expense->amount, 2);
        }

        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_EXPENSE_UPDATED,
            'title' => 'Expense Updated',
            'message' => $message,
            'data' => ['expense_id' => $expense->id],
            'is_read' => false,
        ]);
    }

    /**
     * Create an expense deleted notification
     */
    public static function createExpenseDeleted($userId, $amount, $categoryName)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_EXPENSE_DELETED,
            'title' => 'Expense Deleted',
            'message' => "Expense of $" . number_format($amount, 2) . " from " . $categoryName . " has been deleted",
            'data' => [],
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
}
