<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('inactive');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('device_limit')->default(1);
            $table->unsignedInteger('license_version')->default(1);
            $table->unsignedBigInteger('primary_device_id')->nullable();
            $table->timestamps();
        });
        Schema::create('pos_devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->uuid('installation_id');
            $table->string('name', 100);
            $table->string('role', 16);
            $table->string('status', 16)->default('active');
            $table->char('token_hash', 64)->nullable()->unique();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'installation_id']);
            $table->index(['business_id', 'status']);
        });
        Schema::create('pos_activation_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->char('code_hash', 64)->unique();
            $table->string('hint', 16);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('pos_code_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_activation_code_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_user_id')->nullable()->constrained('business_users')->nullOnDelete();
            $table->timestamp('previous_ends_at')->nullable();
            $table->timestamp('new_ends_at');
            $table->timestamp('redeemed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_code_redemptions');
        Schema::dropIfExists('pos_activation_codes');
        Schema::dropIfExists('pos_devices');
        Schema::dropIfExists('pos_subscriptions');
    }
};
