<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('pos_report_batches',function(Blueprint $t){$t->uuid('id')->primary();$t->foreignId('business_id')->constrained()->cascadeOnDelete();$t->unsignedBigInteger('device_id');$t->uuid('source_uuid');$t->unsignedBigInteger('version');$t->char('request_hash',64);$t->char('content_hash',64);$t->json('metadata');$t->string('status',16)->default('staging');$t->timestamp('created_at');$t->timestamp('activated_at')->nullable();$t->unique(['business_id','source_uuid','version'],'pos_report_source_version');});
  Schema::create('pos_report_chunks',function(Blueprint $t){$t->uuid('batch_id');$t->foreign('batch_id')->references('id')->on('pos_report_batches')->cascadeOnDelete();$t->unsignedInteger('chunk_index');$t->char('sha256',64);$t->unsignedInteger('row_count');$t->boolean('received')->default(false);$t->primary(['batch_id','chunk_index']);});
  Schema::create('pos_report_rows',function(Blueprint $t){$t->bigIncrements('id');$t->uuid('batch_id');$t->foreign('batch_id')->references('id')->on('pos_report_batches')->cascadeOnDelete();$t->string('dataset',16);$t->string('row_key',128);$t->dateTime('occurred_at')->nullable();$t->json('payload');$t->unique(['batch_id','dataset','row_key'],'pos_report_row_identity');$t->index(['batch_id','dataset','occurred_at'],'pos_report_period');});
  Schema::create('pos_report_states',function(Blueprint $t){$t->foreignId('business_id')->primary()->constrained()->cascadeOnDelete();$t->uuid('active_batch_id')->nullable();$t->timestamp('captured_at')->nullable();$t->timestamp('verified_at')->nullable();});
 }
 public function down():void {Schema::dropIfExists('pos_report_states');Schema::dropIfExists('pos_report_rows');Schema::dropIfExists('pos_report_chunks');Schema::dropIfExists('pos_report_batches');}
};
