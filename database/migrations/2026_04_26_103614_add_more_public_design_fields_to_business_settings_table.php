<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->string('background_color')->nullable()->after('secondary_color');
            $table->string('text_color')->nullable()->after('background_color');
            $table->string('card_color')->nullable()->after('text_color');
            $table->string('button_color')->nullable()->after('card_color');
            $table->string('font_family')->nullable()->after('button_color');
            $table->string('border_radius')->nullable()->after('font_family');
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn([
                'background_color',
                'text_color',
                'card_color',
                'button_color',
                'font_family',
                'border_radius',
            ]);
        });
    }
};