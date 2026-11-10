<?php
namespace App\Services;
use App\Models\Business;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\{DB,Validator};
use Illuminate\Support\Str;

class PosReportsSync
{
 public const DATASETS=['inventory','financial','stock','cash','review'];
 public function begin(Business $business,int $device,array $data):array {
  $hash=hash('sha256',json_encode($data,JSON_THROW_ON_ERROR));
  $old=DB::table('pos_report_batches')->where('business_id',$business->id)->where('source_uuid',$data['source_uuid'])->where('version',$data['version'])->first();
  if($old){abort_unless($old->device_id==$device&&hash_equals($old->request_hash,$hash),409,'This report version already belongs to another snapshot.');return ['batch_uuid'=>$old->id];}
  $state=DB::table('pos_report_states')->where('business_id',$business->id)->first();
  if($state?->active_batch_id){$active=DB::table('pos_report_batches')->where('id',$state->active_batch_id)->first();abort_unless($active->source_uuid===$data['source_uuid'],409,'The POS database identity changed. Restore the matching store database before replacing its reports.');abort_unless($data['version']>$active->version,409,'A newer POS report is already stored. Review the restored database before uploading older history.');}
  $indices=[];$hashes=[];$count=0;
  foreach($data['chunks'] as $i=>$chunk){abort_unless($chunk['index']===$i,422,'Report chunks must be consecutive.');$indices[]=$i;$hashes[]=$chunk['sha256'];$count+=$chunk['row_count'];}
  abort_unless(array_sum($data['metadata']['counts'])===$count && $count<=2000000,422,'Report row counts do not match the manifest.');
  $expected=hash('sha256',$data['metadata']['currency']."\n".$data['metadata']['decimals']."\n".$data['metadata']['timezone']."\n".implode("\n",$hashes));
  abort_unless(hash_equals($expected,$data['content_hash']),422,'Report manifest hash does not match.');
  $id=(string)Str::uuid();DB::table('pos_report_batches')->insert(['id'=>$id,'business_id'=>$business->id,'device_id'=>$device,'source_uuid'=>$data['source_uuid'],'version'=>$data['version'],'request_hash'=>$hash,'content_hash'=>$data['content_hash'],'metadata'=>json_encode($data['metadata'],JSON_THROW_ON_ERROR),'status'=>'staging','created_at'=>CarbonImmutable::now('UTC')]);
  foreach($data['chunks'] as $chunk)DB::table('pos_report_chunks')->insert(['batch_id'=>$id,'chunk_index'=>$chunk['index'],'sha256'=>$chunk['sha256'],'row_count'=>$chunk['row_count'],'received'=>false]);
  return ['batch_uuid'=>$id];
 }
 private function batch(Business $business,int $device,string $id):object{return DB::table('pos_report_batches')->where('id',$id)->where('business_id',$business->id)->where('device_id',$device)->first()??abort(404);}
 public function chunk(Business $business,int $device,array $data):array {
  $batch=$this->batch($business,$device,$data['batch_uuid']);
  $chunk=DB::table('pos_report_chunks')->where('batch_id',$batch->id)->where('chunk_index',$data['index'])->first()??abort(404);
  abort_unless(strlen($data['data'])<=1000000&&hash_equals($chunk->sha256,hash('sha256',$data['data'])),422,'Report chunk hash does not match.');
  if($chunk->received)return ['received'=>true,'index'=>$data['index']];
  abort_unless($batch->status==='staging',409,'This report snapshot is already closed.');
  try{$rows=json_decode($data['data'],true,512,JSON_THROW_ON_ERROR);}catch(\JsonException){abort(422,'Invalid report JSON.');}
  abort_unless(is_array($rows)&&array_is_list($rows)&&count($rows)===$chunk->row_count,422,'Report chunk row count differs.');
  $seen=[];
  foreach($rows as $row){
   Validator::make($row,['dataset'=>'required|in:'.implode(',',self::DATASETS),'key'=>'required|string|max:128','occurred_at'=>'present|nullable|date_format:Y-m-d H:i:s','payload'=>'required|array'])->validate();
   $date=$row['occurred_at'];abort_unless(in_array($row['dataset'],['inventory','review'],true)?$date===null:$date!==null,422,'Invalid report date.');
   $this->validatePayload($row['dataset'],$row['payload'],$date);
   $key=$row['dataset'].'|'.$row['key'];abort_if(isset($seen[$key]),422,'Duplicate report record.');$seen[$key]=true;
   abort_if(DB::table('pos_report_rows')->where('batch_id',$batch->id)->where('dataset',$row['dataset'])->where('row_key',$row['key'])->exists(),422,'A report record appears in more than one chunk.');
  }
  // Bounded inserts stay within SQLite/MySQL placeholder limits in both test and production.
  foreach(array_chunk($rows,100) as $part)DB::table('pos_report_rows')->insert(array_map(fn($row)=>['batch_id'=>$batch->id,'dataset'=>$row['dataset'],'row_key'=>$row['key'],'occurred_at'=>$row['occurred_at'],'payload'=>json_encode($row['payload'],JSON_THROW_ON_ERROR)],$part));
  DB::table('pos_report_chunks')->where('batch_id',$batch->id)->where('chunk_index',$data['index'])->update(['received'=>true]);return ['received'=>true,'index'=>$data['index']];
 }
 private function validatePayload(string $dataset,array $p,?string $date):void {
  $text='present|string|max:16000';$number=['required',function($attribute,$value,$fail){if(!PosReportDecimal::valid($value))$fail('Report amounts must be exact decimal strings.');}];
  $rules=match($dataset){
   'inventory'=>['product'=>$text,'category'=>$text,'unit'=>$text,'stock'=>$number,'reserved'=>$number,'available'=>$number,'cost'=>$number,'value'=>$number],
   'financial'=>['order_id'=>'required|integer|min:1','type'=>'required|in:sale,legacy_sale,amendment,amendment_reversal,return,legacy_return,void','date'=>'required|in:'.$date,'payment'=>$text,'actor'=>$text,'cashier'=>$text,'net'=>$number,'restocked'=>'required|boolean','notes'=>$text,'items'=>'present|array|max:2000','items.*.product_id'=>'present|nullable|integer|min:1','items.*.product'=>$text,'items.*.category'=>$text,'items.*.unit'=>$text,'items.*.selling_unit'=>$text,'items.*.base_qty'=>$number,'items.*.gross'=>$number,'items.*.net'=>$number,'items.*.cost'=>$number],
   'stock'=>['product'=>$text,'unit'=>$text,'type'=>$text,'qty'=>$number,'before'=>$number,'after'=>$number,'notes'=>$text],
   'cash'=>['type'=>'required|in:in,out','amount'=>$number,'reference'=>$text,'notes'=>$text,'cashier'=>$text],
   'review'=>['order_id'=>'required|integer|min:0','description'=>$text]
  };Validator::make($p,$rules)->validate();
 }
 public function complete(Business $business,int $device,string $id):array {
  $batch=$this->batch($business,$device,$id);
  if($batch->status==='active')return ['version'=>(int)$batch->version,'content_hash'=>$batch->content_hash];
  abort_unless($batch->status==='staging',409,'This report snapshot was superseded.');
  abort_if(DB::table('pos_report_chunks')->where('batch_id',$id)->where('received',false)->exists(),409,'Some report chunks have not arrived.');
  $metadata=json_decode($batch->metadata,true,512,JSON_THROW_ON_ERROR);
  foreach(self::DATASETS as $dataset)abort_unless(DB::table('pos_report_rows')->where('batch_id',$id)->where('dataset',$dataset)->count()===$metadata['counts'][$dataset],422,'A report dataset is incomplete.');
  $state=DB::table('pos_report_states')->where('business_id',$business->id)->first();
  if($state?->active_batch_id){$old=DB::table('pos_report_batches')->where('id',$state->active_batch_id)->first();abort_unless($old->source_uuid===$batch->source_uuid && $old->version<$batch->version,409,'A newer or different POS database already replaced this report.');DB::table('pos_report_batches')->where('id',$old->id)->update(['status'=>'retired']);}
  DB::table('pos_report_batches')->where('id',$id)->update(['status'=>'active','activated_at'=>CarbonImmutable::now('UTC')]);
  DB::table('pos_report_states')->updateOrInsert(['business_id'=>$business->id],['active_batch_id'=>$id,'captured_at'=>CarbonImmutable::parse($metadata['captured_at'])->utc(),'verified_at'=>CarbonImmutable::now('UTC')]);
  // Retain one previous completed snapshot. Readers also use a single database read transaction.
  $retired=DB::table('pos_report_batches')->where('business_id',$business->id)->where('status','retired')->orderByDesc('version')->skip(1)->take(10000)->pluck('id');
  if($retired->isNotEmpty())DB::table('pos_report_batches')->whereIn('id',$retired)->delete();
  // Abandoned incomplete uploads never appear in reports and expire after seven days.
  DB::table('pos_report_batches')->where('business_id',$business->id)->where('status','staging')->where('created_at','<',CarbonImmutable::now('UTC')->subDays(7))->delete();
  return ['version'=>(int)$batch->version,'content_hash'=>$batch->content_hash];
 }
 public function heartbeat(Business $business,int $device,array $data):array {
  $state=DB::table('pos_report_states')->where('business_id',$business->id)->first()??abort(404);$batch=$this->batch($business,$device,$state->active_batch_id);
  abort_unless($batch->source_uuid===$data['source_uuid']&&(int)$batch->version===$data['version']&&hash_equals($batch->content_hash,$data['content_hash']),409,'The active report differs from this POS snapshot.');
  $captured=CarbonImmutable::parse($data['captured_at'])->utc();
  if($captured->gte(CarbonImmutable::parse($state->captured_at)))DB::table('pos_report_states')->where('business_id',$business->id)->update(['captured_at'=>$captured,'verified_at'=>CarbonImmutable::now('UTC')]);
  return ['verified'=>true];
 }
}
