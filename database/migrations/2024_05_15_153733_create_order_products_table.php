<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 50)->nullable();
            $table->string('product_id', 50)->nullable();
            $table->string("quantity", 50)->nullable();
            $table->string('price', 50)->nullable();
            $table->string('discount')->nullable();
            $table->string('tax', 50)->nullable();
            $table->string('product_total', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_products');
    }
};
