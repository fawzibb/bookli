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
        Schema::create('blocked_times', function (Blueprint $table) {
    $table->id();

    $table->foreignId('business_id')->constrained()->cascadeOnDelete();

    $table->date('blocked_date')->nullable();

    $table->tinyInteger('day_of_week')->nullable();

    $table->boolean('is_recurring')->default(false);

    $table->time('start_time')->nullable();
    $table->time('end_time')->nullable();

    $table->boolean('full_day')->default(false);

    $table->string('reason')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_times');
    }
};
