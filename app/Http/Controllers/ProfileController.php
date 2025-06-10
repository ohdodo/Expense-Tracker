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
        
        if (!$currentUser || !$currentUser->id) {
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
        
        if (!$currentUser || !$currentUser->id) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $currentUser->id,
            'currency' => 'nullable|string|max:10',
            'monthly_budget' => 'nullable|numeric|min:0',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'currency' => $request->currency ?? 'USD',
            'monthly_budget' => $request->monthly_budget,
        ];

        // Track changes for notification
        $changes = [];
        if ($currentUser->name !== $request->name) {
            $changes[] = 'Name';
        }
        if ($currentUser->email !== $request->email) {
            $changes[] = 'Email';
        }
        if ($currentUser->currency !== ($request->currency ?? 'USD')) {
            $changes[] = 'Currency';
        }
        if ($currentUser->monthly_budget != $request->monthly_budget) {
            $changes[] = 'Monthly Budget';
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if it exists
            if ($currentUser->profile_picture && Storage::disk('public')->exists($currentUser->profile_picture)) {
                Storage::disk('public')->delete($currentUser->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $updateData['profile_picture'] = $path;
            $changes[] = 'Profile Picture';
            
            // Make sure storage link exists
            if (!file_exists(public_path('storage'))) {
                \Artisan::call('storage:link');
            }
        }

        $currentUser->update($updateData);

        // Create notification if there were changes
        if (!empty($changes)) {
            \App\Models\Notification::createProfileUpdated($currentUser->id, $changes);
        }

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        // Check authentication using AuthController
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser || !$currentUser->id) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Check if current password is correct
        if (!Hash::check($request->current_password, $currentUser->password)) {
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }

        $currentUser->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Password updated successfully.');
    }

    public function removeProfilePicture()
    {
        // Check authentication using AuthController
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser || !$currentUser->id) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        // Delete profile picture file if it exists
        if ($currentUser->profile_picture && Storage::disk('public')->exists($currentUser->profile_picture)) {
            Storage::disk('public')->delete($currentUser->profile_picture);
        }

        // Remove profile picture from database
        $currentUser->update(['profile_picture' => null]);

        return redirect()->route('profile.edit')->with('success', 'Profile picture removed successfully.');
    }
}
