<?php

namespace App\Http\Controllers;

use App\Schools;
use Illuminate\Http\Request;

class WebhooksController extends Controller {

    private function verifyShopifyHook($data, $hmac_header) {
        $secret = '7c398817991950896e87dfe8730a93f1aaf15878451bd8ac2c86cc39f3f6da0';
        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $secret, true));
        return hash_equals($hmac_header, $calculated_hmac);
    }

    public function shopify(Request $request) {
        //$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
        //$data = file_get_contents('php://input');
        //$verified = static::verifyShopifyHook($data, $hmac_header);
        //\Log::debug('Webhook verified: ' . var_export($verified, true));
        //\Log::debug($data);
        $data = $request->all();
        $data['type'] = $request->header()['x-shopify-topic'][0];

        $file = \Storage::disk('local')->get('shopify.json');
        $file = json_decode($file, true);
        array_unshift($file, $data);
        array_slice($file, 0, 5);
        $file = json_encode($file);
        file_put_contents(base_path('storage/app/shopify.json'), stripslashes($file));
        unset($file); // ?
        // orders/paid
        // orders/create
    }

    public function shopifyGet() {
        $json = \Storage::disk('local')->get('shopify.json');
        return (array) json_decode($json);
    }
}
