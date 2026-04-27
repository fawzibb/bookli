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
        Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();

    $table->foreignId('business_id')->constrained()->cascadeOnDelete();

    $table->timestamp('trial_starts_at')->nullable();
    $table->timestamp('trial_ends_at')->nullable();

    $table->timestamp('subscription_starts_at')->nullable();
    $table->timestamp('subscription_ends_at')->nullable();

    $table->string('status')->default('trial');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
