<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class CustomWebPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $title;
    public $body;
    public $url;
    public $campaignId;

    // Queue properties for reliability
    public $tries = 3;
    public $timeout = 30; // 30 seconds for the external network call
    public $backoff = [10, 60, 300]; // 10s, 1m, 5m

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $body, $url, $campaignId = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
        $this->campaignId = $campaignId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush($notifiable, $notification)
    {
        if ($this->campaignId) {
            \App\Models\NotificationDelivery::where('campaign_id', $this->campaignId)
                ->where('user_id', $notifiable->id)
                ->update(['status' => 'processing', 'attempts' => \Illuminate\Support\Facades\DB::raw('attempts + 1')]);
        }

        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->icon('/images/app-icon.png')
            ->data([
                'url' => $this->url,
                'campaign_id' => $this->campaignId,
            ]);
    }
}
