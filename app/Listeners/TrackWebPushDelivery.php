<?php

namespace App\Listeners;

use NotificationChannels\WebPush\Events\NotificationSent;
use App\Models\NotificationDelivery;

class TrackWebPushDelivery
{
    /**
     * Handle the event.
     */
    public function handle(NotificationSent $event): void
    {
        $payload = $event->message->toArray();
        $campaignId = $payload['data']['campaign_id'] ?? null;
        
        if ($campaignId && $event->subscription) {
            $delivery = NotificationDelivery::where('campaign_id', $campaignId)
                ->where('user_id', $event->subscription->subscribable_id)
                ->first();
                
            if ($delivery) {
                $delivery->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'error_message' => null
                ]);
            }
        }
    }
}
