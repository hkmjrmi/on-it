<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);

        return view('user.notifications.index', compact('notifications'));
    }

    /**
     * Mark the specified notification as read and open its issue.
     */
    public function show(Request $request, string $notification)
    {
        $notification = $request->user()->notifications()->findOrFail($notification);
        $notification->markAsRead();

        $issue = Issue::query()->where('key', $notification->data['issue_key'] ?? null)->first();

        if (!$issue) {
            return redirect(route('user.notifications.index'))
                ->withErrorNotification(__('This issue has been deleted.'));
        }

        return redirect(route('user.issues.show', $issue));
    }

    /**
     * Mark all of the user's notifications as read.
     */
    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->withSuccessNotification(__('All notifications have been marked as read.'));
    }
}
