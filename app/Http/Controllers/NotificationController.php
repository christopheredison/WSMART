<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar notifikasi untuk user yang sedang login
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Menampilkan notifikasi yang belum dibaca untuk navbar
     */
    public function getUnreadNotifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        $count = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();
        
        return response()->json([
            'notifications' => $notifications,
            'count' => $count
        ]);
    }

    /**
     * Menampilkan detail notifikasi
     */
    public function show($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Pastikan notifikasi milik user yang sedang login
        if ($notification->user_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'notification' => $notification
        ]);
    }

    /**
     * Menandai notifikasi sebagai telah dibaca
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Pastikan notifikasi milik user yang sedang login
        if ($notification->user_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $notification->read_at = now();
        $notification->save();
        
        return response()->json(['success' => true]);
    }

    /**
     * Menandai notifikasi sebagai belum dibaca
     */
    public function markAsUnread($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Pastikan notifikasi milik user yang sedang login
        if ($notification->user_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $notification->read_at = null;
        $notification->save();
        
        return response()->json(['success' => true]);
    }

    /**
     * Menandai semua notifikasi sebagai telah dibaca
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Menandai beberapa notifikasi sebagai telah dibaca
     */
    public function markMultipleAsRead(Request $request)
    {
        if (!$request->has('ids') || empty($request->ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada notifikasi yang dipilih']);
        }

        Notification::whereIn('id', $request->ids)
            ->where('user_id', Auth::id())
            ->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Menandai beberapa notifikasi sebagai belum dibaca
     */
    public function markMultipleAsUnread(Request $request)
    {
        if (!$request->has('ids') || empty($request->ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada notifikasi yang dipilih']);
        }

        Notification::whereIn('id', $request->ids)
            ->where('user_id', Auth::id())
            ->update(['read_at' => null]);
        
        return response()->json(['success' => true]);
    }
}