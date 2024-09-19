<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 50)->nullable();
            $table->string('invoice_date', 255)->nullable();
            $table->string("delivery_date", 255)->nullable();
            $table->mediumText('notes')->nullable();
            $table->tinyInteger('payment')->comment("
            1 paid,
            2 refund,
            3 partial,
            4 cancel,
            5 unpaid,
            ");
            $table->string('payment_method')->nullable();
            $table->string('payment_from')->nullable();
            $table->string('shipping_charge',50)->nullable();
            $table->string('total_amount',50)->nullable();
            $table->string('order_code',255)->nullable();
            $table->string("created_by",10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
