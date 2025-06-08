<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'profile_picture',
        'currency',
        'monthly_budget',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'monthly_budget' => 'decimal:2',
    ];

    // Automatically hash password when setting
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    // Custom authentication method
    public static function authenticate($email, $password)
    {
        $user = static::where('email', $email)->first();

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;
    }

    // Login user by setting session
    public function login()
    {
        session(['user_id' => $this->id]);
        session(['user_email' => $this->email]);
        session(['user_name' => $this->name]);
        
        // Debug log
        \Log::info('User login - Session set:', [
            'user_id' => $this->id,
            'session_id' => session()->getId()
        ]);
    }

    // Logout user by clearing session
    public static function logout()
    {
        // Debug log before logout
        \Log::info('User logout - Before clearing session:', [
            'user_id' => session('user_id'),
            'session_id' => session()->getId()
        ]);
        
        session()->forget(['user_id', 'user_email', 'user_name']);
        session()->flush();
        
        // Debug log after logout
        \Log::info('User logout - After clearing session:', [
            'session_id' => session()->getId()
        ]);
    }

    // Check if user is logged in
    public static function isLoggedIn()
    {
        $isLoggedIn = session()->has('user_id') && session('user_id') !== null;
        
        // Debug log
        \Log::info('User isLoggedIn check:', [
            'result' => $isLoggedIn,
            'user_id' => session('user_id'),
            'session_id' => session()->getId()
        ]);
        
        return $isLoggedIn;
    }

    // Get current logged in user
    public static function getCurrentUser()
    {
        if (!static::isLoggedIn()) {
            \Log::info('getCurrentUser: Not logged in');
            return null;
        }

        $userId = session('user_id');
        $user = static::find($userId);
        
        // Debug log
        \Log::info('getCurrentUser result:', [
            'user_id' => $userId,
            'found' => $user ? true : false
        ]);
        
        return $user;
    }

    // Get currency symbol
    public function getCurrencySymbol()
    {
        $symbols = [
            'PHP' => '₱',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        return $symbols[$this->currency] ?? '₱';
    }

    // Relationships
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}