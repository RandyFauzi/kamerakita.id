<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\CustomWebPushNotification;
use Notification;

class PushNotificationController extends Controller
{
    /**
     * Show the push notification broadcast form.
     */
    public function index()
    {
        return view('admin.push-notifications.index');
    }

    /**
     * Send the push notification.
     */
    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'url' => 'nullable|url',
            'target' => 'required|in:all,workers,admins',
        ]);

        $query = User::query();

        if ($request->target === 'workers') {
            $query->where('role', 'worker');
        } elseif ($request->target === 'admins') {
            $query->whereIn('role', ['admin', 'superadmin']);
        }

        // We only want to send to users who have active push subscriptions
        $users = $query->whereHas('pushSubscriptions')->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada pengguna dengan langganan notifikasi aktif di grup ini.');
        }

        Notification::send($users, new CustomWebPushNotification(
            $request->title,
            $request->body,
            $request->url ?? url('/')
        ));

        return back()->with('success', 'Notifikasi berhasil dikirim ke ' . $users->count() . ' pengguna!');
    }
}
