<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;
use Throwable;

class MidtransService {
    public function __construct() {
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    public function getSnapToken(Transaction $transaction): string {
        if ($transaction->snap_token || $transaction->midtrans_order_id) {
            $oldSnapToken = $transaction->snap_token;
            $oldOrderId = $transaction->midtrans_order_id;

            if ($oldOrderId) {
                $transaction->update([
                    'midtrans_order_id' => null,
                    'snap_token' => null,
                ]);

                try {
                    MidtransTransaction::cancel($oldOrderId);
                } catch (Throwable $exception) {
                    $transaction->update([
                        'midtrans_order_id' => $oldOrderId,
                        'snap_token' => $oldSnapToken,
                    ]);

                    throw $exception;
                }
            } else {
                $transaction->update(['snap_token' => null]);
            }
        }

        $orderId = 'PPL-' . $transaction->id . '-' . Str::random(16);
        $user    = $transaction->user;

        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $transaction->total,
            ],
            'item_details' => [[
                'id'       => 'TRX-' . $transaction->id,
                'price'    => (int) $transaction->total,
                'quantity' => 1,
                'name'     => 'Pesanan #' . $transaction->id,
            ]],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
            'callbacks' => [
                'finish' => route('client.orders.show', $transaction->id),
            ],
            'enabled_payments' => $this->enabledPayments($transaction->payment_method),
            // Pesanan yang tidak dibayar akan expire, lalu stok dikembalikan lewat webhook
            'expiry' => ['unit' => 'minutes', 'duration' => 60],
        ]);

        $transaction->update([
            'midtrans_order_id' => $orderId,
            'snap_token'        => $snapToken,
        ]);

        return $snapToken;
    }

    public function refreshStatus(Transaction $transaction): Transaction {
        if ($transaction->status !== 'pending' || ! $transaction->midtrans_order_id) {
            return $transaction->fresh();
        }

        try {
            $status = MidtransTransaction::status($transaction->midtrans_order_id);
        } catch (Throwable $exception) {
            Log::warning('Gagal mengambil status transaksi dari Midtrans.', [
                'transaction_id' => $transaction->id,
                'exception' => $exception->getMessage(),
            ]);

            return $transaction->fresh();
        }

        $this->applyStatus(
            $transaction,
            $status->transaction_status ?? null,
            $status->fraud_status ?? null,
            $status->payment_type ?? null,
        );

        return $transaction->fresh();
    }

    public function applyNotification(array $notification): void {
        $orderId = (string) ($notification['order_id'] ?? '');
        $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

        if (! $transaction && preg_match('/^PPL-(\d+)-[A-Za-z0-9]+$/D', $orderId, $matches)) {
            $transaction = Transaction::query()
                ->whereKey((int) $matches[1])
                ->where('status', 'pending')
                ->whereIn('payment_method', ['bank', 'e-wallet'])
                ->whereNull('midtrans_order_id')
                ->first();

            if ($transaction) {
                $transaction->update(['midtrans_order_id' => $orderId]);
            }
        }

        if (! $transaction) {
            return;
        }

        $this->applyStatus(
            $transaction,
            $notification['transaction_status'] ?? null,
            $notification['fraud_status'] ?? null,
            $notification['payment_type'] ?? null,
        );
    }

    public function cancelPending(Transaction $transaction): void {
        if ($transaction->midtrans_order_id) {
            MidtransTransaction::cancel($transaction->midtrans_order_id);
        }
    }

    private function applyStatus(
        Transaction $transaction,
        ?string $status,
        ?string $fraudStatus,
        ?string $paymentType,
    ): void {
        DB::transaction(function () use ($transaction, $status, $fraudStatus, $paymentType) {
            $lockedTransaction = Transaction::with('details')
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedTransaction || $lockedTransaction->status !== 'pending') {
                return;
            }

            if ($status === 'settlement' || ($status === 'capture' && $fraudStatus === 'accept')) {
                $lockedTransaction->update([
                    'status' => 'paid',
                    'payment_type' => $paymentType,
                    'paid_at' => now(),
                ]);
            } elseif (in_array($status, ['cancel', 'expire'], true)) {
                $this->restoreStock($lockedTransaction);
                $lockedTransaction->update(['status' => 'canceled']);
            } elseif (in_array($status, ['deny', 'failure'], true)) {
                $this->restoreStock($lockedTransaction);
                $lockedTransaction->update(['status' => 'failed']);
            }
        });
    }

    private function restoreStock(Transaction $transaction): void {
        foreach ($transaction->details as $detail) {
            Item::whereKey($detail->item_id)->increment('stock', $detail->quantity);
        }
    }

    private function enabledPayments(string $paymentMethod): array {
        return match ($paymentMethod) {
            'bank' => [
                'bank_transfer',
                'bca_va',
                'bni_va',
                'bri_va',
                'permata_va',
                'cimb_va',
                'danamon_va',
                'other_va',
            ],
            'e-wallet' => ['gopay', 'shopeepay', 'qris', 'dana'],
            default => [],
        };
    }
}
