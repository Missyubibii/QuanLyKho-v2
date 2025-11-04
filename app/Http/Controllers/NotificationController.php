<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Lấy tất cả thông báo của user đang đăng nhập
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->take(10) // Giới hạn 10 thông báo mới nhất
            ->get(['id', 'type', 'title', 'message', 'is_read', 'created_at']);

        return response()->json($notifications);
    }

    // Đánh dấu tất cả thông báo của user là đã đọc
    public function markAsRead(Request $request)
    {
        $request->user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return response()->json(['status' => 'success']);
    }
}
