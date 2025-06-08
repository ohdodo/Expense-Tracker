<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function markAllAsRead()
    {
        // Check authentication
        if ($redirect = AuthController::checkAuth()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $currentUser = AuthController::getCurrentUser();
        
        Notification::where('user_id', $currentUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function markAsRead(Notification $notification)
    {
        // Check authentication
        if ($redirect = AuthController::checkAuth()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $currentUser = AuthController::getCurrentUser();
        
        if ($notification->user_id !== $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }
}