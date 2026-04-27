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
        Schema::create('business_settings', function (Blueprint $table) {
    $table->id();

    $table->foreignId('business_id')->constrained()->cascadeOnDelete();

    $table->integer('slot_interval')->default(30);
    $table->integer('max_days_ahead')->default(14);

    $table->string('currency')->default('USD');
    $table->string('timezone')->default('Asia/Beirut');

    $table->string('whatsapp_number')->nullable();

    $table->boolean('booking_enabled')->default(false);
    $table->boolean('ordering_enabled')->default(false);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
