<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_catalogs', function(Blueprint $t) {
            $t->id(); $t->foreignId('business_id')->unique()->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('version')->default(0); $t->char('payload_hash',64)->nullable();
            $t->string('currency',16); $t->unsignedTinyInteger('decimals')->default(2); $t->timestamp('last_contact_at')->nullable(); $t->timestamps();
        });
        Schema::table('menu_items', function(Blueprint $t) {
            $t->uuid('pos_unit_uuid')->nullable(); $t->uuid('pos_product_uuid')->nullable();
            $t->string('pos_unit_name',100)->nullable(); $t->decimal('pos_contains_qty',18,6)->nullable();
            $t->decimal('pos_retail_price',18,4)->nullable(); $t->decimal('pos_available_base',18,6)->nullable();
            $t->boolean('pos_published')->default(false); $t->string('pos_image_path')->nullable();
            $t->unique(['business_id','pos_unit_uuid']);
        });
        Schema::table('orders', function(Blueprint $t) {
            $t->uuid('sync_uuid')->nullable()->unique(); $t->unsignedInteger('sync_revision')->default(1);
            $t->uuid('pos_request_uuid')->nullable(); $t->string('pos_request_hash',64)->nullable();
            $t->boolean('pos_managed')->default(false); $t->string('completion_source',16)->nullable();
            $t->timestamp('delivered_at')->nullable(); $t->string('pos_currency',16)->nullable(); $t->decimal('pos_total',18,4)->nullable();
            $t->unique(['business_id','pos_request_uuid']);
        });
        Schema::table('order_items', function(Blueprint $t) {
            $t->uuid('pos_unit_uuid')->nullable(); $t->decimal('pos_contains_qty',18,6)->nullable();
            $t->decimal('pos_exact_price',18,4)->nullable(); $t->decimal('pos_exact_total',18,4)->nullable();
        });
        Schema::create('pos_order_events', function(Blueprint $t) {
            $t->id(); $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete(); $t->unsignedInteger('revision');
            $t->json('payload'); $t->timestamp('created_at'); $t->unique(['order_id','revision']); $t->index(['business_id','id']);
        });
        Schema::create('pos_sync_commands', function(Blueprint $t) {
            $t->id(); $t->foreignId('business_id')->constrained()->cascadeOnDelete(); $t->uuid('command_uuid');
            $t->char('payload_hash',64); $t->json('result'); $t->timestamp('created_at'); $t->unique(['business_id','command_uuid']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('pos_sync_commands'); Schema::dropIfExists('pos_order_events');
        Schema::table('order_items', fn(Blueprint $t) => $t->dropColumn(['pos_unit_uuid','pos_contains_qty','pos_exact_price','pos_exact_total']));
        Schema::table('orders', function(Blueprint $t) { $t->dropUnique(['sync_uuid']); $t->dropUnique(['business_id','pos_request_uuid']); $t->dropColumn(['sync_uuid','sync_revision','pos_request_uuid','pos_request_hash','pos_managed','completion_source','delivered_at','pos_currency','pos_total']); });
        Schema::table('menu_items', function(Blueprint $t) { $t->dropUnique(['business_id','pos_unit_uuid']); $t->dropColumn(['pos_unit_uuid','pos_product_uuid','pos_unit_name','pos_contains_qty','pos_retail_price','pos_available_base','pos_published','pos_image_path']); });
        Schema::dropIfExists('pos_catalogs');
    }
};
