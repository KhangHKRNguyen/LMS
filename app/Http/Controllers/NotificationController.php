<?php

namespace App\Http\Controllers;

use App\Models\NotificationRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = NotificationRecipient::query()
            ->where('user_id', Auth::id())
            ->with(['notification.sender'])
            ->latest()
            ->paginate(12);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(NotificationRecipient $recipient)
    {
        abort_unless($recipient->user_id === Auth::id(), 403);

        $recipient->update(['is_read' => true]);

        return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    public function markAllAsRead()
    {
        NotificationRecipient::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }
}
