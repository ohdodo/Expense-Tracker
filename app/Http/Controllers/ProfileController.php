<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        // Check authentication using AuthController
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        // Debug the user session
        \Log::info('Profile Edit - Current User:', [
            'user_id' => $currentUser ? $currentUser->id : 'null',
            'session_user_id' => session('user_id'),
            'session_data' => session()->all()
        ]);
        
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        return view('profile.edit', compact('currentUser'));
    }

    public function update(Request $request)
    {
        // Check authentication using AuthController
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $currentUser->id,
            'phone' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'currency' => 'required|string|in:PHP,USD,EUR,GBP',
            'monthly_budget' => 'nullable|numeric|min:0',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'currency', 'monthly_budget']);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($currentUser->profile_picture) {
                Storage::disk('public')->delete($currentUser->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $data['profile_picture'] = $path;
        }

        $currentUser->update($data);

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        // Check authentication using AuthController
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $currentUser->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $currentUser->update([
            'password' => $request->new_password, // Will be hashed by the model
        ]);

        return redirect()->route('profile.edit')->with('success', 'Password changed successfully!');
    }
}