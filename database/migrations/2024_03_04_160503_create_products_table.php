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
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->unsignedBigInteger('color_id')->nullable();
            $table->string('name_bn', 255)->nullable();
            $table->string('name_en', 255)->nullable();
            $table->string('quantity', 50)->nullable();
            $table->string("price")->nullable();
            $table->mediumText('description')->nullable();
            $table->tinyInteger('status')->comment("
            0 inactive,
            1 active,
            ");
            $table->string('date', 255)->nullable();
            $table->string('product_code');
            $table->string('created_by', 50)->nullable();
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
