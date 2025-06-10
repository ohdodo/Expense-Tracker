<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function markAsRead(Notification $notification)
    {
        // Check authentication
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser || $notification->user_id !== $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        // Check authentication
        if ($redirect = AuthController::checkAuth()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            Notification::where('user_id', $currentUser->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);
            
            return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function index()
    {
        // Check authentication
        if ($redirect = AuthController::checkAuth()) {
            return $redirect;
        }

        $currentUser = AuthController::getCurrentUser();
        
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $notifications = Notification::where('user_id', $currentUser->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notifications', 'currentUser'));
    }
}
