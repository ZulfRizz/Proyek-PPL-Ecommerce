<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\MidtransService;

test('order page includes the authenticated user and current transactions', function () {
    $user = User::factory()->create(['username' => 'Pembeli Uji']);
    $transaction = Transaction::factory()->create([
        'client_id' => $user->id,
        'status' => 'paid',
    ]);

    $this->actingAs($user)
        ->get('/pesanan-saya')
        ->assertOk()
        ->assertInertia(fn($page) => $page
            ->component('PesananSaya')
            ->where('user.username', 'Pembeli Uji')
            ->where('transactions.0.id', $transaction->id)
            ->where('transactions.0.status', 'paid'));
});

test('pending orders can be canceled and their stock is restored once', function () {
    $user = User::factory()->create();
    $item = Item::factory()->create([
        'category_id' => Category::factory(),
        'stock' => 3,
    ]);
    $transaction = Transaction::factory()->create([
        'client_id' => $user->id,
        'status' => 'pending',
        'payment_method' => 'cash',
    ]);

    TransactionDetail::create([
        'transaction_id' => $transaction->id,
        'item_id' => $item->id,
        'quantity' => 2,
        'price_at_time' => $item->price,
    ]);

    $this->actingAs($user)
        ->from('/pesanan-saya')
        ->post("/client/orders/{$transaction->id}/cancel")
        ->assertRedirect('/pesanan-saya');

    expect($transaction->fresh()->status)->toBe('canceled')
        ->and($item->fresh()->stock)->toBe(5);

    $this->from('/pesanan-saya')
        ->post("/client/orders/{$transaction->id}/cancel")
        ->assertRedirect('/pesanan-saya');

    expect($item->fresh()->stock)->toBe(5);
});

test('paid orders cannot be canceled', function () {
    $user = User::factory()->create();
    $item = Item::factory()->create([
        'category_id' => Category::factory(),
        'stock' => 3,
    ]);
    $transaction = Transaction::factory()->create([
        'client_id' => $user->id,
        'status' => 'paid',
        'payment_method' => 'cash',
    ]);

    TransactionDetail::create([
        'transaction_id' => $transaction->id,
        'item_id' => $item->id,
        'quantity' => 2,
        'price_at_time' => $item->price,
    ]);

    $this->actingAs($user)
        ->from('/pesanan-saya')
        ->post("/client/orders/{$transaction->id}/cancel")
        ->assertRedirect('/pesanan-saya');

    expect($transaction->fresh()->status)->toBe('paid')
        ->and($item->fresh()->stock)->toBe(3);
});

test('Midtrans settlement updates a pending order to paid', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'client_id' => $user->id,
        'status' => 'pending',
        'payment_method' => 'bank',
        'midtrans_order_id' => 'PPL-TEST-SETTLEMENT',
    ]);

    app(MidtransService::class)->applyNotification([
        'order_id' => 'PPL-TEST-SETTLEMENT',
        'transaction_status' => 'settlement',
        'payment_type' => 'bank_transfer',
    ]);

    expect($transaction->fresh()->status)->toBe('paid')
        ->and($transaction->fresh()->payment_type)->toBe('bank_transfer')
        ->and($transaction->fresh()->paid_at)->not->toBeNull();
});

test('Midtrans notification can match a legacy order without a stored order ID', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'client_id' => $user->id,
        'status' => 'pending',
        'payment_method' => 'e-wallet',
        'midtrans_order_id' => null,
        'snap_token' => 'legacy-snap-token',
    ]);
    $orderId = "PPL-{$transaction->id}-1700000000";

    expect(preg_match('/^PPL-(\d+)-[A-Za-z0-9]+$/D', $orderId))->toBe(1)
        ->and(Transaction::whereKey($transaction->id)->whereNull('midtrans_order_id')->exists())->toBeTrue();

    app(MidtransService::class)->applyNotification([
        'order_id' => $orderId,
        'transaction_status' => 'settlement',
        'payment_type' => 'gopay',
    ]);

    expect($transaction->fresh()->midtrans_order_id)->toBe($orderId)
        ->and($transaction->fresh()->status)->toBe('paid')
        ->and($transaction->fresh()->payment_type)->toBe('gopay');
});
