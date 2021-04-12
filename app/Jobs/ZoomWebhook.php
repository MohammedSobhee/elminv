<?php
namespace App\Jobs;

use \Spatie\WebhookClient\ProcessWebhookJob as SpatieProcessWebhookJob;
use App\Services\ZoomService;

class ZoomWebhook extends SpatieProcessWebhookJob {
    public function handle() {
        $payload = $this->webhookCall->payload;
        \Log::debug('zoom deauth payload ' . print_r($payload));
        if ($payload) {
            $zoom = new ZoomService;
            $completed = $zoom->deAuth($payload['payload']);
            \Log::debug('deauth completed ' . print_r($completed));
        }
    }
}
