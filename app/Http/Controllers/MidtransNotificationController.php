<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\Request;

class MidtransNotificationController extends Controller {
    public function handle(Request $request, MidtransService $midtrans) {
        $expected = hash(
            'sha512',
            $request->order_id . $request->status_code . $request->gross_amount
                . config('services.midtrans.server_key')
        );

        if (! hash_equals($expected, (string) $request->signature_key)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $midtrans->applyNotification($request->all());

        return response()->json(['message' => 'OK']);
    }
}
