<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model {
    use HasFactory;

    protected $fillable = [
        'client_id',
        'total',
        'note',
        'payment_method',
        'status',
        'midtrans_order_id',
        'snap_token',
        'payment_type',
        'paid_at'
    ];

    protected $casts = ['paid_at' => 'datetime'];

    /**
     * User / Client yang melakukan transaksi
     */
    public function user(): BelongsTo {
        return $this->belongsTo(
            User::class,
            'client_id',
            'id'
        );
    }

    /**
     * Detail transaksi
     *
     * transactions
     *      ↓
     * transaction_details
     */
    public function details(): HasMany {
        return $this->hasMany(
            TransactionDetail::class,
            'transaction_id',
            'id'
        );
    }

    /**
     * Produk yang ada di transaksi
     *
     * Relasi ini tetap dipertahankan
     * untuk kebutuhan lain.
     */
    public function items(): BelongsToMany {
        return $this->belongsToMany(
            Item::class,
            'transaction_details',
            'transaction_id',
            'item_id'
        )
            ->withPivot(
                'quantity',
                'price_at_time'
            )
            ->withTimestamps();
    }
}
