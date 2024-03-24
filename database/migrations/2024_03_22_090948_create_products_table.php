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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('category_id', 50)->nullable();
            $table->string('seller_id', 50)->nullable();
            $table->string("name", 255)->nullable();
            $table->string('per_unit_product_price', 50)->nullable();
            $table->string('product_unit')->nullable();
            $table->string('product_quantity', 50)->nullable();
            $table->string('total_price', 50)->nullable();
            $table->string('stock_alert',50)->nullable();
            $table->mediumText('product_details')->nullable();
            $table->tinyInteger('status')->comment("
            0 inactive,
            1 active,
            ");
            $table->string('date',50)->nullable();
            $table->string('product_sku_code',255)->nullable();
            $table->string('product_code',255)->nullable();
            $table->string('product_image',255)->nullable();
            $table->string('product_document',255)->nullable();
            $table->string("created_by",10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
