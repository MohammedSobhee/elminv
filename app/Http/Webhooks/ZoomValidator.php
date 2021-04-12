<?php

namespace App\Http\Webhooks;

use \Spatie\WebhookClient\SignatureValidator\SignatureValidator as SpatieSignatureValidator;
use \Illuminate\Http\Request;
use \Spatie\WebhookClient\WebhookConfig;

class ZoomValidator implements SpatieSignatureValidator {
    public function isValid(Request $request, WebhookConfig $config): bool {
        if ($request->header('authorization') == config('services.zoom')['verification_token']) {
            return true;
        }
        return false;
    }
}
