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
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $query = User::query();

        if ($request->target === 'workers') {
            $query->where('role', 'worker');
        } elseif ($request->target === 'admins') {
            $query->whereIn('role', ['admin', 'superadmin']);
        }

        // We only want to send to users who have active push subscriptions
        $query->whereHas('pushSubscriptions');
        
        $totalUsers = $query->count();

        if ($totalUsers === 0) {
            return back()->with('error', 'Tidak ada pengguna dengan langganan notifikasi aktif di grup ini.');
        }

        $campaign = \App\Models\NotificationCampaign::create([
            'title' => $request->title,
            'body' => $request->body,
            'target' => $request->target,
            'scheduled_at' => $request->filled('scheduled_at') ? \Carbon\Carbon::parse($request->scheduled_at) : null,
            'total_targets' => $totalUsers,
            'status' => 'queued'
        ]);

        $query->chunk(500, function ($users) use ($request, $campaign) {
            $deliveries = [];
            foreach ($users as $user) {
                $deliveries[] = [
                    'campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                    'channel' => 'webpush',
                    'status' => 'queued',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            \App\Models\NotificationDelivery::insert($deliveries);

            $notification = new CustomWebPushNotification(
                $request->title,
                $request->body,
                $request->url ?? url('/'),
                $campaign->id
            );

            if ($request->filled('scheduled_at')) {
                $scheduledTime = \Carbon\Carbon::parse($request->scheduled_at);
                Notification::send($users, $notification->delay($scheduledTime));
            } else {
                Notification::send($users, $notification);
            }
        });

        if ($request->filled('scheduled_at')) {
            $scheduledTime = \Carbon\Carbon::parse($request->scheduled_at);
            return back()->with('success', 'Notifikasi berhasil dimasukkan ke antrean jadwal untuk ' . $scheduledTime->format('d M Y H:i') . ' (Waktu Server).');
        }

        return back()->with('success', 'Notifikasi berhasil dimasukkan ke antrean untuk ' . $totalUsers . ' pengguna!');
    }
}
