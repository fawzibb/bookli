<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\{PosCatalogSync, PosOrderSync};
use Illuminate\Http\Request;
class PosSyncController extends Controller
{
    private function run(Request $request, PosCatalogSync $sync, callable $action) { return response()->json($sync->run($request->attributes->get('pos_device'),$request->attributes->get('pos_token_hash'),$action)); }
    public function catalog(Request $request, PosCatalogSync $sync)
    {
        $data=$request->validate(['version'=>'required|integer|min:1','currency'=>'required|string|max:16','decimals'=>'required|integer|min:0|max:4',
            'units'=>'present|array|max:10000','units.*.unit_uuid'=>'required|uuid','units.*.product_uuid'=>'required|uuid',
            'units.*.name'=>'required|string|max:255','units.*.unit_name'=>'required|string|max:100','units.*.category'=>'present|nullable|string|max:255',
            'units.*.description'=>'nullable|string|max:2000','units.*.price'=>'required|numeric|min:0|max:99999999',
            'units.*.contains_qty'=>'required|numeric|gt:0|max:999999999','units.*.available_base'=>'required|numeric|min:0|max:999999999']);
        return $this->run($request,$sync,fn($business)=>$sync->publish($business,$data));
    }
    public function feed(Request $request, PosCatalogSync $sync, PosOrderSync $orders)
    {
        $data=$request->validate(['cursor'=>'nullable|integer|min:0']);
        return $this->run($request,$sync,fn($business)=>$sync->feed($business,(int)($data['cursor'] ?? 0),$orders));
    }
    public function command(Request $request, PosCatalogSync $sync, PosOrderSync $orders)
    {
        $data=$request->validate(['command_uuid'=>'required|uuid','order_uuid'=>'required|uuid','expected_revision'=>'required|integer|min:1',
            'pos_total'=>'required_if:action,complete_pos|nullable|numeric|min:0|max:99999999','pos_currency'=>'required_if:action,complete_pos|nullable|string|max:16',
            'action'=>'required|in:accept,reject,preparing,ready,deliver,deliver_correct,collect,collect_correct,refund,partial_refund,complete_pos,cancel_pos']);
        return $this->run($request,$sync,fn($business)=>$sync->command($business,$data,$orders));
    }
    public function image(Request $request, PosCatalogSync $sync)
    {
        $data=$request->validate(['unit_uuid'=>'required|uuid','image'=>'required_without:remove|nullable|string|max:2800000','remove'=>'sometimes|accepted']);
        return $this->run($request,$sync,fn($business)=>$sync->image($business,$data));
    }
}
