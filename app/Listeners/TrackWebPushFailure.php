<?php

namespace App\Listeners;

use NotificationChannels\WebPush\Events\NotificationFailed;
use App\Models\NotificationDelivery;
use Illuminate\Support\Facades\Log;

class TrackWebPushFailure
{
    /**
     * Handle the event.
     */
    public function handle(NotificationFailed $event): void
    {
        $payload = $event->message->toArray();
        $campaignId = $payload['data']['campaign_id'] ?? null;
        $report = $event->report;

        $reason = $report->getReason();
        $isExpired = $report->isSubscriptionExpired();
        
        // The package automatically deletes expired subscriptions (410 Gone),
        // but we still want to log the failure reason in our ledger.
        
        if ($campaignId && $event->subscription) {
            $delivery = NotificationDelivery::where('campaign_id', $campaignId)
                ->where('user_id', $event->subscription->subscribable_id)
                ->first();
                
            if ($delivery) {
                $delivery->update([
                    'status' => $isExpired ? 'expired' : 'failed',
                    'error_message' => substr($reason, 0, 500)
                ]);
            }
        } else {
            Log::warning("Web Push Failed [No Campaign ID]: " . $reason);
        }
    }
}
