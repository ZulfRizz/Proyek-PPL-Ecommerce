<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller {
    public function token(Transaction $transaction, MidtransService $midtrans) {
        abort_unless($transaction->client_id === Auth::id(), 403);

        $transaction = $midtrans->refreshStatus($transaction);

        abort_unless($transaction->status === 'pending', 422, 'Pesanan sudah diproses.');
        abort_if($transaction->payment_method === 'cash', 422, 'Pesanan ini bayar tunai.');

        return response()->json([
            'snap_token' => $midtrans->getSnapToken($transaction),
        ]);
    }

    public function cancel(Transaction $transaction, MidtransService $midtrans) {
        $canceled = DB::transaction(function () use ($transaction, $midtrans) {
            $lockedTransaction = Transaction::with('details')
                ->whereKey($transaction->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedTransaction->client_id === Auth::id(), 403);

            if ($lockedTransaction->status !== 'pending') {
                return false;
            }

            $midtrans->cancelPending($lockedTransaction);

            foreach ($lockedTransaction->details as $detail) {
                Item::whereKey($detail->item_id)
                    ->increment('stock', $detail->quantity);
            }

            $lockedTransaction->update(['status' => 'canceled']);

            return true;
        });

        return back()->with(
            $canceled ? 'success' : 'error',
            $canceled
                ? 'Pesanan berhasil dibatalkan.'
                : 'Pesanan tidak dapat dibatalkan karena sudah diproses.'
        );
    }
}
