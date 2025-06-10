<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // If already logged in, redirect to home
        if (User::isLoggedIn()) {
            return redirect()->route('expenses.index');
        }
        
        return view('auth.loginView');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->withInput($request->except('password'));
        }

        // Store user info in session
        session(['user_id' => $user->id, 'user_email' => $user->email]);
        session()->save();

        return redirect()->route('expenses.index');
    }

    public function showRegisterForm()
    {
        // If already logged in, redirect to home
        if (User::isLoggedIn()) {
            return redirect()->route('expenses.index');
        }
        
        return view('auth.registrationView');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'currency' => 'USD', // Default currency
        ]);

        // Store user info in session
        session(['user_id' => $user->id, 'user_email' => $user->email]);
        session()->save();

        return redirect()->route('expenses.index');
    }

    public function logout()
    {
        // Use the User model's logout method
        User::logout();
        
        return redirect()->route('login');
    }

    /**
     * Check if user is authenticated
     * 
     * @return \Illuminate\Http\RedirectResponse|null
     */
    public static function checkAuth()
    {
        if (!User::isLoggedIn()) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        
        return null;
    }

    /**
     * Get current authenticated user
     * 
     * @return \App\Models\User|null
     */
    public static function getCurrentUser()
    {
        return User::getCurrentUser();
    }
}
