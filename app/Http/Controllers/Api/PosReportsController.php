<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\{PosCatalogSync,PosReportsSync};
use Illuminate\Http\Request;
class PosReportsController extends Controller
{
 private function run(Request $r,PosCatalogSync $sync,callable $action){return response()->json($sync->run($r->attributes->get('pos_device'),$r->attributes->get('pos_token_hash'),fn($b)=>$action($b,$r->attributes->get('pos_device')->id)));}
 public function begin(Request $r,PosCatalogSync $sync,PosReportsSync $reports){
  $data=$r->validate(['source_uuid'=>'required|uuid','version'=>'required|integer|min:1','content_hash'=>'required|regex:/^[a-f0-9]{64}$/','metadata'=>'required|array:captured_at,currency,decimals,timezone,counts','metadata.captured_at'=>'required|date','metadata.currency'=>'required|string|max:16','metadata.decimals'=>'required|integer|min:0|max:4','metadata.timezone'=>'required|timezone:all','metadata.counts'=>'required|array:inventory,financial,stock,cash,review','metadata.counts.inventory'=>'required|integer|min:0|max:2000000','metadata.counts.financial'=>'required|integer|min:0|max:2000000','metadata.counts.stock'=>'required|integer|min:0|max:2000000','metadata.counts.cash'=>'required|integer|min:0|max:2000000','metadata.counts.review'=>'required|integer|min:0|max:2000000','chunks'=>'present|array|max:10000','chunks.*'=>'array:index,sha256,row_count','chunks.*.index'=>'required|integer|min:0','chunks.*.sha256'=>'required|regex:/^[a-f0-9]{64}$/','chunks.*.row_count'=>'required|integer|min:1|max:500']);
  return $this->run($r,$sync,fn($b,$d)=>$reports->begin($b,$d,$data));
 }
 public function chunk(Request $r,PosCatalogSync $sync,PosReportsSync $reports){$data=$r->validate(['batch_uuid'=>'required|uuid','index'=>'required|integer|min:0','data'=>'required|string|max:1000000']);return $this->run($r,$sync,fn($b,$d)=>$reports->chunk($b,$d,$data));}
 public function complete(Request $r,PosCatalogSync $sync,PosReportsSync $reports){$data=$r->validate(['batch_uuid'=>'required|uuid']);return $this->run($r,$sync,fn($b,$d)=>$reports->complete($b,$d,$data['batch_uuid']));}
 public function heartbeat(Request $r,PosCatalogSync $sync,PosReportsSync $reports){$data=$r->validate(['source_uuid'=>'required|uuid','version'=>'required|integer|min:1','content_hash'=>'required|regex:/^[a-f0-9]{64}$/','captured_at'=>'required|date']);return $this->run($r,$sync,fn($b,$d)=>$reports->heartbeat($b,$d,$data));}
}
