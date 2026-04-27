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
        ->whereNotIn('status', ['completed', 'cancelled'])
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
        ->where('status', 'completed')
        ->whereDate('created_at', '>=', $from)
        ->whereDate('created_at', '<=', $to)
        ->latest()
        ->get();

    $totalAmount = $orders->sum('total_amount');

    return view('owner.orders.completed', compact(
        'orders',
        'from',
        'to',
        'totalAmount'
    ));
}

    public function updateStatus(Request $request, Order $order)
    {
        $businessId = Auth::guard('business')->user()->business_id;
        abort_if($order->business_id !== $businessId, 403);

        $request->validate([
            'status' => ['required', 'in:pending,confirmed,preparing,ready,completed,cancelled'],
        ]);

        $order->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Order status updated.');
    }
    public function partial()
{
    $business = auth()->guard('business')->user()->business;

    $orders = Order::with(['customer', 'items'])
        ->where('business_id', $business->id)
        ->whereNotIn('status', ['completed', 'cancelled'])
        ->latest()
        ->get();

    return view('owner.orders.partials.list', compact('orders'));
}
}