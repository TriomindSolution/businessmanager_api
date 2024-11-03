<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('slug')->nullable();
            $table->string('parent_id', 50)->nullable();
            $table->string("created_by",10)->nullable();
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('parent_id');
            $table->dropColumn('created_by');
        });
    }

};
