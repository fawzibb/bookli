<?php
namespace App\Services;
use App\Models\{Business, BusinessSetting, Customer, MenuItem, Order, OrderItem, Subscription};
use Illuminate\Support\Facades\{DB, Validator};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PosOrderSync
{
    public function websiteActive(Business $business): bool
    {
        $sub=Subscription::where('business_id',$business->id)->first();
        $until=$sub?->subscription_ends_at ?: $sub?->trial_ends_at;
        return $business->is_active && $until && now()->lt($until) && !in_array($sub->status,['suspended','cancelled','expired'],true);
    }
    public static function money($value): int
    {
        $value = (string)$value;
        if (!preg_match('/^\d{1,8}(?:\.\d{1,4})?$/D', $value)) throw ValidationException::withMessages(['price'=>'Invalid amount. Use at most four decimal places.']);
        $parts = explode('.', $value);
        return ((int)$parts[0])*10000 + (int)str_pad($parts[1] ?? '',4,'0');
    }
    public static function amount(int $scaled): string { return intdiv($scaled,10000).'.'.str_pad((string)($scaled%10000),4,'0',STR_PAD_LEFT); }
    public function snapshot(Order $order): array
    {
        $first=DB::table('pos_order_events')->where('order_id',$order->id)->orderBy('id')->value('payload');
        if($first)$items=json_decode($first,true,512,JSON_THROW_ON_ERROR)['items'];
        else {
            $order->load('items.menuItem');
            $items=$order->items->map(function($line) {
                $unit=$line->pos_unit_uuid ? ($line->menuItem?->pos_unit_name ?? '') : '';
                $suffix=' — '.$unit;
                $product=$unit!=='' && str_ends_with($line->item_name,$suffix)?substr($line->item_name,0,-strlen($suffix)):$line->item_name;
                return ['unit_uuid'=>$line->pos_unit_uuid,'name'=>$line->item_name,'product_name'=>$product,'unit_name'=>$unit,
                    'quantity'=>(int)$line->quantity,'contains_qty'=>(string)($line->pos_contains_qty ?? 1),'price'=>(string)$line->unit_price,'notes'=>$line->notes ?? ''];
            })->all();
        }
        return ['uuid'=>$order->sync_uuid,'number'=>$order->order_number,'revision'=>(int)$order->sync_revision,
            'status'=>$order->status,'customer_name'=>$order->customer_name ?? $order->customer?->name ?? '',
            'customer_phone'=>$order->customer_phone ?? $order->customer?->phone ?? '', 'order_type'=>$order->order_type,
            'delivery_address'=>$order->delivery_address ?? '', 'delivery_fee'=>(string)($order->delivery_fee ?? 0),
            'total'=>(string)$order->total_amount,'currency'=>$order->pos_currency ?? 'USD', 'notes'=>$order->notes ?? '',
            'scheduled_for'=>$order->scheduled_for?->toIso8601String(),'created_at'=>$order->created_at->toIso8601String(),
            'delivered'=>(bool)$order->delivered_at,'external_collection'=>$order->completion_source === 'website',
            'completion_source'=>$order->completion_source,'items'=>$items];
    }
    public function record(Order $order): array
    {
        if (!$order->sync_uuid) $order->forceFill(['sync_uuid'=>(string)Str::uuid()])->save();
        $snapshot=$this->snapshot($order);
        DB::table('pos_order_events')->insert(['business_id'=>$order->business_id,'order_id'=>$order->id,'revision'=>$order->sync_revision,
            'payload'=>json_encode($snapshot,JSON_THROW_ON_ERROR),'created_at'=>now()]);
        return $snapshot;
    }
    public function transition(Order $order, string $action, string $source, ?int $expected = null): array
    {
        if ($expected !== null && $order->sync_revision != $expected)
            abort(409,'The order changed. Synchronize it before trying again.');
        $status=$order->status;
        if($status==='refunded' && in_array($action,['refund','partial_refund'],true) && $source==='pos')return $this->snapshot($order);
        $allowed=match($action) {
            'accept'=>['pending'], 'reject'=>['pending','confirmed','preparing','ready'],
            'preparing'=>['confirmed'], 'ready'=>['confirmed','preparing'], 'deliver'=>['confirmed','preparing','ready'],'deliver_correct'=>['cancelled'],
            'complete_pos'=>['pending','confirmed','preparing','ready','delivered'], 'cancel_pos'=>['pending','confirmed','preparing','ready'],
            'collect'=>['delivered'], 'collect_correct'=>['completed'], 'complete_external'=>['pending','confirmed','preparing','ready','delivered'],
            'refund','partial_refund'=>['completed','partially_refunded'], default=>[]
        };
        abort_unless(in_array($status,$allowed,true),409,'This action is not valid for the current order state.');
        if (in_array($action,['refund','partial_refund'],true)) abort_unless($source==='pos' && $order->completion_source==='pos',409);
        if (in_array($action,['collect','collect_correct','complete_pos','cancel_pos'],true)) abort_unless($source==='pos',403);
        $new=match($action) { 'accept'=>'confirmed','reject','cancel_pos'=>'cancelled','deliver','deliver_correct'=>'delivered','collect','collect_correct','complete_external','complete_pos'=>'completed',
            'refund'=>'refunded','partial_refund'=>'partially_refunded',default=>$action };
        $changes=['status'=>$new,'sync_revision'=>$order->sync_revision+1];
        if ($source==='pos') $changes['pos_managed']=true;
        if (in_array($action,['deliver','deliver_correct','collect','complete_external','complete_pos'],true)) $changes['delivered_at']=$order->delivered_at ?? now();
        if (in_array($action,['collect','collect_correct','complete_pos'],true)) $changes['completion_source']='pos';
        if ($action==='complete_external') $changes['completion_source']='website';
        $order->forceFill($changes)->save();
        return $this->record($order);
    }
    public function place(Business $business, BusinessSetting $settings, array $input): Order
    {
        Validator::make($input,['name'=>'required|string|max:150','phone'=>'required|string|max:50','notes'=>'nullable|string|max:2000',
            'request_id'=>'required|uuid','items'=>'required|array|min:1|max:200','items.*.id'=>'required|integer|min:1',
            'items.*.quantity'=>'required|integer|min:1|max:10000','order_type'=>'required|in:inside,delivery','delivery_address'=>'nullable|string|max:255'])->validate();
        $hash=hash('sha256',json_encode($input,JSON_THROW_ON_ERROR));
        return DB::transaction(function()use($business,$settings,$input,$hash) {
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $existing=Order::where('business_id',$business->id)->where('pos_request_uuid',$input['request_id'])->first();
            if ($existing) { abort_unless(hash_equals($existing->pos_request_hash,$hash),409,'This request identifier was already used for another order.'); return $existing; }
            $subscription=Subscription::where('business_id',$business->id)->first();
            $until=$subscription?->subscription_ends_at ?: $subscription?->trial_ends_at;
            abort_unless($until && now()->lt($until) && !in_array($subscription->status,['suspended','cancelled','expired'],true),403,'Online ordering subscription has expired.');
            abort_unless($business->is_active && $settings->ordering_enabled,403,'Online ordering is unavailable.');
            $delivery=$input['order_type']==='delivery';
            if ($delivery && (!$settings->delivery_enabled || empty($input['delivery_address']))) throw ValidationException::withMessages(['delivery_address'=>'Delivery is unavailable or the address is missing.']);
            $catalog=DB::table('pos_catalogs')->where('business_id',$business->id)->first();
            $lines=[]; $total=0; $seen=[];
            foreach ($input['items'] as $entry) {
                if (isset($seen[$entry['id']])) throw ValidationException::withMessages(['items'=>'Duplicate item rows are not allowed.']);
                $seen[$entry['id']]=true;
                $query=MenuItem::where('business_id',$business->id)->where('is_available',true)->whereKey($entry['id']);
                if($catalog)$query->where('pos_published',true)->whereNotNull('pos_unit_uuid');
                $item=$query->lockForUpdate()->firstOrFail();
                $price=self::money($item->price); $quantity=(int)$entry['quantity']; $line=$price*$quantity; $total+=$line;
                if((float)($item->pos_contains_qty ?? 1)*$quantity>999999999)throw ValidationException::withMessages(['items'=>'Order base quantity is too large.']);
                $lines[]=[$item,$quantity,$price,$line];
            }
            $fee=$delivery?self::money($settings->delivery_fee ?? 0):0; $total+=$fee;
            $factor=10**(4-($catalog?->decimals ?? 2)); $total=intdiv($total+intdiv($factor,2),$factor)*$factor;
            if ($total>999999990000) throw ValidationException::withMessages(['items'=>'Order amount is too large.']);
            $customer=Customer::firstOrCreate(['business_id'=>$business->id,'phone'=>$input['phone']],['name'=>$input['name']]);
            $order=new Order(); $order->forceFill(['business_id'=>$business->id,'customer_id'=>$customer->id,'customer_name'=>$input['name'],
                'customer_phone'=>$input['phone'],'order_number'=>'ORD-'.strtoupper(Str::random(10)),'order_type'=>$input['order_type'],
                'delivery_fee'=>self::amount($fee),'delivery_address'=>$delivery?$input['delivery_address']:null,'status'=>'pending',
                'total_amount'=>self::amount($total),'pos_total'=>self::amount($total),'pos_currency'=>$catalog?->currency ?? 'USD',
                'notes'=>$input['notes'] ?? null,'sync_uuid'=>(string)Str::uuid(),'sync_revision'=>1,
                'pos_request_uuid'=>$input['request_id'],'pos_request_hash'=>$hash]); $order->save();
            foreach ($lines as [$item,$qty,$price,$line]) {
                $row=new OrderItem();$row->forceFill(['order_id'=>$order->id,'menu_item_id'=>$item->id,'item_name'=>$item->name,
                    'quantity'=>$qty,'unit_price'=>self::amount($price),'total_price'=>self::amount($line),
                    'pos_unit_uuid'=>$item->pos_unit_uuid,'pos_contains_qty'=>$item->pos_contains_qty ?? 1,
                    'pos_exact_price'=>self::amount($price),'pos_exact_total'=>self::amount($line)]);$row->save();
            }
            $this->record($order); return $order;
        },3);
    }
}
