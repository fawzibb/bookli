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
        Schema::create('activation_codes', function (Blueprint $table) {
    $table->id();

    $table->string('code')->unique();

    $table->integer('days');
    $table->integer('max_uses')->default(1);
    $table->integer('used_count')->default(0);

    $table->timestamp('expires_at')->nullable();

    $table->boolean('is_active')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation_codes');
    }
};
