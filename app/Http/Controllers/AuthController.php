<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Check if user is authenticated
     * 
     * @return \Illuminate\Http\RedirectResponse|null
     */
    public static function checkAuth()
    {
        if (!User::isLoggedIn()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }
        return null;
    }
    
    /**
     * Get the current authenticated user
     * 
     * @return \App\Models\User|null
     */
    public static function getCurrentUser()
    {
        return User::getCurrentUser();
    }
    
    /**
     * Redirect if already authenticated
     * 
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function redirectIfAuthenticated()
    {
        if (User::isLoggedIn()) {
            return redirect()->route('expenses.index');
        }
        return null;
    }

    public function showLoginForm()
    {
        // Redirect if already logged in
        if ($redirect = $this->redirectIfAuthenticated()) {
            return $redirect;
        }
        
        return view('auth.loginView');
    }

    public function login(Request $request)
    {
        // Redirect if already logged in
        if ($redirect = $this->redirectIfAuthenticated()) {
            return $redirect;
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::authenticate($request->email, $request->password);

        if ($user) {
            $user->login();
            return redirect()->intended(route('expenses.index'))->with('success', 'Welcome back!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    public function showRegisterForm()
    {
        // Redirect if already logged in
        if ($redirect = $this->redirectIfAuthenticated()) {
            return $redirect;
        }
        
        return view('auth.registrationView');
    }

    public function register(Request $request)
    {
        // Redirect if already logged in
        if ($redirect = $this->redirectIfAuthenticated()) {
            return $redirect;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Will be hashed by the model
            'currency' => 'PHP',
        ]);

        $user->login();

        return redirect()->route('expenses.index')->with('success', 'Account created successfully! Welcome to Expense Tracker!');
    }

    public function logout()
    {
        User::logout();
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}