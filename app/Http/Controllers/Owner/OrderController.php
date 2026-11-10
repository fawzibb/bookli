<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    public function index()
{
    $business = auth()->guard('business')->user()->business;

    $orders = Order::with(['customer', 'items'])
        ->where('business_id', $business->id)
        ->whereNotIn('status', ['completed', 'cancelled', 'refunded', 'partially_refunded'])
        ->latest()
        ->get();

    return view('owner.orders.index', compact('orders'));
}

public function completed(Request $request)
{
    $user = Auth::guard('business')->user();
    $businessId = $user->business_id;

    $from = $request->get('from', now('Asia/Beirut')->toDateString());
    $to = $request->get('to', now('Asia/Beirut')->toDateString());

    $orders = Order::with(['customer', 'items'])
        ->where('business_id', $businessId)
        ->whereIn('status', ['completed','refunded','partially_refunded'])
        ->whereDate('created_at', '>=', $from)
        ->whereDate('created_at', '<=', $to)
        ->latest()
        ->get();

    $totalAmount = $orders->where('status','!=','refunded')->sum('total_amount');
    $totalsByCurrency=$orders->groupBy(fn($order)=>$order->pos_currency ?? 'USD')->map(fn($group)=>$group->where('status','!=','refunded')->sum('total_amount'));

    return view('owner.orders.completed', compact(
        'orders',
        'from',
        'to',
        'totalAmount','totalsByCurrency'
    ));
}

    public function updateStatus(Request $request, Order $order)
    {
        $businessId=Auth::guard('business')->user()->business_id;
        abort_unless($order->business_id==$businessId,403);
        $data=$request->validate(['status'=>'required|in:confirmed,preparing,ready,completed,cancelled','revision'=>'required|integer|min:1']);
        \DB::transaction(function()use($order,$businessId,$data) {
            \App\Models\Business::whereKey($businessId)->lockForUpdate()->firstOrFail();
            $current=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $service=app(\App\Services\PosOrderSync::class);
            if (!$current->sync_uuid) $service->record($current);
            $action=match($data['status']) {'confirmed'=>'accept','cancelled'=>'reject','completed'=>'complete_external',default=>$data['status']};
            $service->transition($current,$action,'website',(int)$data['revision']);
        },3);
        return back()->with('success','Order status updated.');
    }
    public function partial()
{
    $business = auth()->guard('business')->user()->business;

    $orders = Order::with(['customer', 'items'])
        ->where('business_id', $business->id)
        ->whereNotIn('status', ['completed', 'cancelled', 'refunded', 'partially_refunded'])
        ->latest()
        ->get();

    return view('owner.orders.partials.list', compact('orders'));
}
}
