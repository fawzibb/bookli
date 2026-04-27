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
       Schema::create('business_users', function (Blueprint $table) {
    $table->id();

    $table->foreignId('business_id')->constrained()->cascadeOnDelete();

    $table->string('first_name');
    $table->string('last_name');

    $table->string('phone')->unique();
    $table->string('password');

    $table->boolean('is_owner')->default(true);
    $table->boolean('is_active')->default(true);
    $table->rememberToken();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_users');
    }
};
