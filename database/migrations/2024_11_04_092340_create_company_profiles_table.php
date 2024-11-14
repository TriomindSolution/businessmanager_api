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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('address', 100)->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 100)->nullable();
            $table->mediumText('company_details')->nullable();
            $table->string('registration_no', 255)->nullable();
            $table->string('facebook_url', 255)->nullable();
            $table->string('twitter_url', 255)->nullable();
            $table->string('instagram_url', 255)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->tinyInteger('status')->comment("
            0 inactive,
            1 active,
            ")->nullable;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
