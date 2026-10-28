<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('midtrans_order_id')->nullable()->unique()->after('status');
            $table->string('snap_token')->nullable()->after('midtrans_order_id');
            $table->string('payment_type')->nullable()->after('snap_token');
            $table->timestamp('paid_at')->nullable()->after('payment_type');
        });
    }

    public function down(): void {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['midtrans_order_id']);
            $table->dropColumn(['midtrans_order_id', 'snap_token', 'payment_type', 'paid_at']);
        });
    }
};
