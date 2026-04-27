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
        Schema::table('businesses', function (Blueprint $table) {
            $table->unsignedInteger('capacity_per_slot')->default(1)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
         $table->dropColumn('capacity_per_slot');
        });
    }
};
