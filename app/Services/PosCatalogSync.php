<?php
namespace App\Services;
use App\Models\{Business, Category, MenuItem, Order, PosDevice, PosSubscription};
use App\Exceptions\PosLicenseException;
use Illuminate\Support\Facades\{DB, Storage};

class PosCatalogSync
{
    public function run(PosDevice $known, string $hash, callable $action)
    {
        return DB::transaction(function()use($known,$hash,$action) {
            $business=Business::whereKey($known->business_id)->lockForUpdate()->firstOrFail();
            $device=PosDevice::whereKey($known->id)->firstOrFail();
            $subscription=PosSubscription::where('business_id',$business->id)->first();
            if (!$business->is_active) throw new PosLicenseException('business_disabled','This business is disabled.');
            if ($device->status!=='active' || !$device->token_hash || !hash_equals($device->token_hash,$hash)) throw new PosLicenseException('device_revoked','Device access was revoked.',401);
            if ($subscription?->status==='suspended') throw new PosLicenseException('pos_suspended','POS access is suspended. Contact Bookli support.',403);
            if (!$subscription?->is_valid) throw new PosLicenseException('pos_expired','Renew the POS subscription before synchronizing.',402);
            if ($device->role!=='primary' || $subscription->primary_device_id!=$device->id) throw new PosLicenseException('primary_required','Only the registered primary may synchronize.',409);
            DB::table('pos_catalogs')->where('business_id',$business->id)->update(['last_contact_at'=>now()]);
            return $action($business);
        },3);
    }
    public function publish(Business $business, array $data): array
    {
        abort_unless($business->mode==='menu',422,'This Bookli account must use menu mode to publish POS products.');
        $hash=hash('sha256',json_encode($data,JSON_THROW_ON_ERROR));
        $catalog=DB::table('pos_catalogs')->where('business_id',$business->id)->first();
        if ($catalog && $data['version'] <= $catalog->version) {
            abort_unless($data['version']==$catalog->version && hash_equals($catalog->payload_hash,$hash),409,'The catalog version is stale or has different content.');
            return ['version'=>(int)$catalog->version];
        }
        $seen=[];
        // A published POS catalog becomes the menu source. Old menu rows are retained for history.
        MenuItem::where('business_id',$business->id)->update(['is_available'=>false,'pos_published'=>false]);
        foreach($data['units'] as $entry) {
            abort_if(isset($seen[$entry['unit_uuid']]),422,'Duplicate published unit.'); $seen[$entry['unit_uuid']]=true;
            abort_unless(preg_match('/^\d{1,9}(?:\.\d{1,6})?$/D',(string)$entry['contains_qty']) && (float)$entry['contains_qty']>0,422,'Pack quantities require at most six decimal places.');
            abort_unless(preg_match('/^\d{1,9}(?:\.\d{1,6})?$/D',(string)$entry['available_base']),422,'Availability requires at most six decimal places.');
            $category=Category::firstOrCreate(['business_id'=>$business->id,'name'=>$entry['category'] ?: 'Other'],['is_active'=>true,'sort_order'=>0]);
            $category->update(['is_active'=>true]);
            $item=MenuItem::firstOrNew(['business_id'=>$business->id,'pos_unit_uuid'=>$entry['unit_uuid']]);
            if ($item->exists) abort_unless($item->pos_product_uuid===$entry['product_uuid'],409,'A published unit cannot change its product identity.');
            $price=PosOrderSync::amount(PosOrderSync::money($entry['price']));
            $item->forceFill(['pos_unit_uuid'=>$entry['unit_uuid'],'category_id'=>$category->id,'name'=>$entry['name'],'description'=>$entry['description'] ?? '',
                'price'=>$price,'pos_retail_price'=>$price,'pos_product_uuid'=>$entry['product_uuid'],'pos_unit_name'=>$entry['unit_name'],
                'pos_contains_qty'=>$entry['contains_qty'],'pos_available_base'=>$entry['available_base'],
                'pos_published'=>true,'is_available'=>(float)$entry['available_base'] >= (float)$entry['contains_qty']]);$item->save();
        }
        DB::table('pos_catalogs')->updateOrInsert(['business_id'=>$business->id],['version'=>$data['version'],'payload_hash'=>$hash,
            'currency'=>$data['currency'],'decimals'=>$data['decimals'],'last_contact_at'=>now(),'updated_at'=>now(),'created_at'=>$catalog?->created_at ?? now()]);
        return ['version'=>(int)$data['version']];
    }
    public function feed(Business $business, int $cursor, PosOrderSync $orders): array
    {
        // Backfill retained website orders, including those completed while POS was stopped.
        foreach(Order::where('business_id',$business->id)->whereDoesntHave('syncEvents')->orderBy('id')->limit(50)->get() as $order) {
            if ($order->status==='completed' && !$order->delivered_at)
                $order->forceFill(['completion_source'=>'website','delivered_at'=>$order->updated_at])->save();
            $orders->record($order);$order->unsetRelations();
        }
        $events=[];$bytes=0;$last=$cursor;
        foreach(DB::table('pos_order_events')->where('business_id',$business->id)->where('id','>',$cursor)->orderBy('id')->limit(50)->cursor() as $event) {
            $entry=['id'=>(int)$event->id,'order'=>json_decode($event->payload,true,512,JSON_THROW_ON_ERROR)];
            $size=strlen(json_encode($entry,JSON_THROW_ON_ERROR));
            if($events && $bytes+$size>4*1024*1024)break;
            $events[]=$entry;$bytes+=$size;$last=$event->id;
        }
        return ['events'=>$events,
            'cursor'=>(int)$last,'has_more'=>DB::table('pos_order_events')->where('business_id',$business->id)->where('id','>',$last)->exists()
                || Order::where('business_id',$business->id)->whereDoesntHave('syncEvents')->exists()];
    }
    public function command(Business $business, array $data, PosOrderSync $orders): array
    {
        $hash=hash('sha256',json_encode($data,JSON_THROW_ON_ERROR));
        $old=DB::table('pos_sync_commands')->where('business_id',$business->id)->where('command_uuid',$data['command_uuid'])->first();
        if ($old) { abort_unless(hash_equals($old->payload_hash,$hash),409,'This command identifier already belongs to another operation.'); return json_decode($old->result,true,512,JSON_THROW_ON_ERROR); }
        $order=Order::where('business_id',$business->id)->where('sync_uuid',$data['order_uuid'])->lockForUpdate()->firstOrFail();
        if($data['action']==='complete_pos') {
            abort_unless($data['pos_currency'] === ($order->pos_currency ?? 'USD'),409,'The POS invoice currency differs from the website order.');
            $total=PosOrderSync::money($data['pos_total']);
            // Final local invoices are immutable facts: concurrent ready/preparing changes cannot lose them.
            abort_unless(in_array($order->status,['pending','confirmed','preparing','ready','delivered'],true),409,'The website order was already cancelled or completed. Review the locally saved POS invoice.');
            $order->forceFill(['pos_total'=>PosOrderSync::amount($total),'total_amount'=>PosOrderSync::amount($total)]);
        }
        $result=$orders->transition($order,$data['action'],'pos',in_array($data['action'],['refund','partial_refund','complete_pos','cancel_pos'],true)?null:(int)$data['expected_revision']);
        DB::table('pos_sync_commands')->insert(['business_id'=>$business->id,'command_uuid'=>$data['command_uuid'],'payload_hash'=>$hash,
            'result'=>json_encode($result,JSON_THROW_ON_ERROR),'created_at'=>now()]);
        return $result;
    }
    public function image(Business $business, array $data): array
    {
        $item=MenuItem::where('business_id',$business->id)->where('pos_unit_uuid',$data['unit_uuid'])->where('pos_published',true)->firstOrFail();
        if(!empty($data['remove'])) { $item->forceFill(['pos_image_path'=>null])->save(); return ['saved'=>true]; }
        $bytes=base64_decode($data['image'],true); $info=$bytes ? @getimagesizefromstring($bytes) : false;
        abort_unless($info && strlen($bytes)<=2097152 && $info[0]<=4096 && $info[1]<=4096 && in_array($info['mime'],['image/jpeg','image/png','image/webp'],true),422,'Invalid product image.');
        $extension=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$info['mime']];
        $path='pos-catalog/'.$business->id.'/'.hash('sha256',$bytes).'.'.$extension;
        abort_unless(Storage::disk('public')->put($path,$bytes),503,'Product image could not be saved. Retry after checking server storage.');
        $item->forceFill(['pos_image_path'=>$path])->save();
        return ['saved'=>true];
    }
}
