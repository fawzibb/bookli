<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Hash, Storage, Event, Notification};
use Illuminate\Support\Str;
use App\Models\{Business, BusinessUser, BusinessSetting, MenuItem, Order, PosSubscription};
use App\Services\{PosLicensingService, PosOrderSync};

class PosOnlineSyncTest extends TestCase
{
    use RefreshDatabase;
    private array $shop; private array $unit; private string $key;
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app=parent::createApplication();$app['config']->set('database.default','sqlite');$app['config']->set('database.connections.sqlite.database',':memory:');return $app;
    }
    protected function setUp(): void
    {
        parent::setUp();$this->travelTo(now()->setDate(2026,11,7)->setTime(12,0));
        $this->key=storage_path('framework/sync-test-'.Str::uuid().'.pem');
        $rsa=openssl_pkey_new(['private_key_type'=>OPENSSL_KEYTYPE_RSA,'private_key_bits'=>2048,'config'=>config('pos.openssl_config')]);
        openssl_pkey_export($rsa,$pem,null,['config'=>config('pos.openssl_config')]);file_put_contents($this->key,$pem);config(['pos.private_key_path'=>$this->key]);
        $this->shop=$this->shop();$this->unit=['unit_uuid'=>(string)Str::uuid(),'product_uuid'=>(string)Str::uuid(),'name'=>'Coffee — Box','unit_name'=>'Box','category'=>'Drinks','description'=>'','price'=>'20.0000','contains_qty'=>'12','available_base'=>'120'];
        Event::fake();Notification::fake();$this->withoutVite();
    }
    protected function tearDown(): void { if(isset($this->key)&&is_file($this->key))unlink($this->key);parent::tearDown(); }
    private function shop(): array
    {
        $uuid=(string)Str::uuid();$b=Business::create(['name'=>'Test shop','slug'=>$uuid,'business_type'=>'restaurant','mode'=>'menu','phone'=>$uuid,'is_active'=>true]);
        $u=BusinessUser::create(['business_id'=>$b->id,'first_name'=>'Test','last_name'=>'Owner','phone'=>$uuid,'password'=>Hash::make('secret'),'is_owner'=>true,'is_active'=>true]);
        $s=BusinessSetting::create(['business_id'=>$b->id,'ordering_enabled'=>true,'booking_enabled'=>false,'delivery_enabled'=>true,'delivery_fee'=>3]);
        DB::table('subscriptions')->insert(['business_id'=>$b->id,'status'=>'active','subscription_ends_at'=>now()->addYear()]);
        $service=app(PosLicensingService::class);$service->redeem($u,$service->createCode($b,null));$token=$service->activate($u,(string)Str::uuid(),'Primary','primary')['device_token'];
        return compact('b','u','s','token');
    }
    private function publish(int $version=1,array $units=null)
    {return $this->withToken($this->shop['token'])->postJson('/api/pos/sync/catalog',['version'=>$version,'currency'=>'USD','decimals'=>2,'units'=>$units??[$this->unit]]);}
    private function place(array $changes=[]): Order
    {
        $input=array_merge(['name'=>'Customer','phone'=>'123456','notes'=>'No sugar','request_id'=>(string)Str::uuid(),'items'=>[['id'=>MenuItem::where('business_id',$this->shop['b']->id)->where('is_available',true)->firstOrFail()->id,'quantity'=>2]],'order_type'=>'delivery','delivery_address'=>'Test address'],$changes);
        return app(PosOrderSync::class)->place($this->shop['b'],$this->shop['s'],$input);
    }
    private function command(Order $order,string $action,?string $id=null,?int $revision=null)
    {return $this->withToken($this->shop['token'])->postJson('/api/pos/sync/commands',['command_uuid'=>$id??(string)Str::uuid(),'order_uuid'=>$order->sync_uuid,'expected_revision'=>$revision??$order->sync_revision,'action'=>$action]);}
    public function test_published_units_keep_independent_retail_prices_and_hide_old_menu_without_deleting_it(): void
    {
        $category=\App\Models\Category::create(['business_id'=>$this->shop['b']->id,'name'=>'Old','is_active'=>true]);
        $old=MenuItem::create(['business_id'=>$this->shop['b']->id,'category_id'=>$category->id,'name'=>'Old item','price'=>9,'is_available'=>true]);
        $piece=$this->unit;$piece['unit_uuid']=(string)Str::uuid();$piece['name']='Coffee — Piece';$piece['price']='2.1234';$piece['contains_qty']='1';
        $this->publish(1,[$this->unit,$piece])->assertOk();
        $this->assertFalse((bool)$old->fresh()->is_available);$this->assertDatabaseCount('menu_items',3);
        $this->assertSame('2.1234',(string)MenuItem::where('pos_unit_uuid',$piece['unit_uuid'])->first()->price);
        $this->publish(1,[$this->unit,$piece])->assertOk();$this->assertDatabaseCount('menu_items',3);
        $piece['price']='3';$this->publish(1,[$piece])->assertStatus(409);
        $this->publish(2,[])->assertOk();$this->assertSame(0,MenuItem::where('is_available',true)->count());
    }
    public function test_checkout_is_atomic_server_priced_and_idempotent(): void
    {
        $this->publish()->assertOk();$request=(string)Str::uuid();
        $one=$this->place(['request_id'=>$request]);$two=$this->place(['request_id'=>$request]);
        $this->assertSame($one->id,$two->id);$this->assertSame('43.0000',(string)$one->total_amount);
        $this->assertDatabaseCount('orders',1);$this->assertDatabaseCount('order_items',1);$this->assertDatabaseCount('pos_order_events',1);
        $this->assertSame('12.000000',(string)$one->items->first()->pos_contains_qty);
    }
    public function test_duplicate_command_after_lost_response_does_not_transition_twice(): void
    {
        $this->publish()->assertOk();$order=$this->place();$id=(string)Str::uuid();
        $this->command($order,'accept',$id)->assertOk()->assertJsonPath('revision',2);
        $this->command($order,'accept',$id)->assertOk()->assertJsonPath('revision',2);
        $this->assertDatabaseCount('pos_order_events',2);$this->assertDatabaseCount('pos_sync_commands',1);
        $this->command($order,'reject',$id)->assertStatus(409);
        $this->assertSame('confirmed',$order->fresh()->status);
    }
    public function test_conflicting_website_and_pos_actions_cannot_overwrite_each_other(): void
    {
        $this->publish()->assertOk();$order=$this->place();
        DB::transaction(fn()=>app(PosOrderSync::class)->transition($order,'reject','website',1));
        $this->command($order,'accept',revision:1)->assertStatus(409);
        $this->assertSame('cancelled',$order->fresh()->status);
    }
    public function test_delivery_and_collection_are_separate_and_terminal_orders_cannot_be_reopened(): void
    {
        $this->publish()->assertOk();$order=$this->place();
        $this->command($order,'collect')->assertStatus(409);
        $this->command($order,'accept')->assertOk();$order->refresh();
        $this->command($order,'deliver')->assertOk()->assertJsonPath('delivered',true)->assertJsonPath('external_collection',false);
        $order->refresh();$this->command($order,'collect')->assertOk()->assertJsonPath('completion_source','pos');
        $order->refresh();$this->command($order,'reject')->assertStatus(409);
        $this->command($order,'refund')->assertOk()->assertJsonPath('status','refunded');
        $order->refresh();$this->command($order,'accept')->assertStatus(409);
    }
    public function test_external_completed_orders_are_preserved_as_stock_only_events(): void
    {
        $this->publish()->assertOk();$order=$this->place();
        DB::transaction(fn()=>app(PosOrderSync::class)->transition($order,'complete_external','website',1));
        $feed=$this->withToken($this->shop['token'])->getJson('/api/pos/sync/orders?cursor=0')->assertOk();
        $feed->assertJsonPath('events.1.order.external_collection',true)->assertJsonPath('events.1.order.delivered',true);
        $this->command($order->fresh(),'collect')->assertStatus(409);
    }
    public function test_expired_pos_does_not_stop_website_orders_but_cannot_sync(): void
    {
        $this->publish()->assertOk();PosSubscription::where('business_id',$this->shop['b']->id)->update(['ends_at'=>now()->subMinute()]);
        $order=$this->place();$this->assertSame('pending',$order->status);
        $this->withToken($this->shop['token'])->getJson('/api/pos/sync/orders')->assertStatus(402);
        $this->assertDatabaseCount('pos_order_events',1);
    }
    public function test_tenant_boundaries_apply_to_commands_feeds_and_public_checkout(): void
    {
        $this->publish()->assertOk();$order=$this->place();$other=$this->shop();
        $this->withToken($other['token'])->getJson('/api/pos/sync/orders')->assertOk()->assertJsonCount(0,'events');
        $this->withToken($other['token'])->postJson('/api/pos/sync/commands',['command_uuid'=>(string)Str::uuid(),'order_uuid'=>$order->sync_uuid,'expected_revision'=>1,'action'=>'accept'])->assertNotFound();
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        app(PosOrderSync::class)->place($other['b'],$other['s'],['name'=>'C','phone'=>'1','request_id'=>(string)Str::uuid(),'items'=>[['id'=>$order->items->first()->menu_item_id,'quantity'=>1]],'order_type'=>'inside']);
    }
    public function test_invalid_quantities_and_expired_website_cannot_create_partial_orders(): void
    {
        $this->publish()->assertOk();
        try{$this->place(['items'=>[['id'=>MenuItem::first()->id,'quantity'=>-1]]]);$this->fail('Negative quantity accepted');}catch(\Illuminate\Validation\ValidationException $e){}
        $this->assertDatabaseCount('orders',0);$this->assertDatabaseCount('order_items',0);
        DB::table('subscriptions')->where('business_id',$this->shop['b']->id)->update(['subscription_ends_at'=>now()->subMinute()]);
        try{$this->place();$this->fail('Expired website accepted checkout');}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        $this->assertDatabaseCount('orders',0);
    }
    public function test_images_are_tenant_scoped_and_removable(): void
    {
        Storage::fake('public');$this->publish()->assertOk();
        $png='iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/hmcAAAAASUVORK5CYII=';
        $this->withToken($this->shop['token'])->postJson('/api/pos/sync/image',['unit_uuid'=>$this->unit['unit_uuid'],'image'=>$png])->assertOk();
        $item=MenuItem::where('pos_unit_uuid',$this->unit['unit_uuid'])->first();$this->assertNotEmpty($item->pos_image_path);
        $this->get('/pos-menu-images/'.$item->id)->assertOk();
        $this->withToken($this->shop['token'])->postJson('/api/pos/sync/image',['unit_uuid'=>$this->unit['unit_uuid'],'remove'=>true])->assertOk();
        $this->assertNull($item->fresh()->pos_image_path);
    }
    public function test_fractional_prices_round_only_the_order_total_to_currency_precision(): void
    {
        $unit=$this->unit;$unit['price']='0.3333';$this->publish(1,[$unit])->assertOk();
        $order=$this->place(['order_type'=>'inside','items'=>[['id'=>MenuItem::first()->id,'quantity'=>3]]]);
        $this->assertSame('1.0000',(string)$order->total_amount);$this->assertSame('0.3333',(string)$order->items->first()->unit_price);
    }
    public function test_order_units_and_retail_prices_are_frozen_when_catalog_changes(): void
    {
        $this->publish()->assertOk();$order=$this->place();$changed=$this->unit;
        $changed['unit_name']='Carton';$changed['name']='Coffee — Carton';$changed['price']='30';$changed['contains_qty']='24';
        $this->publish(2,[$changed])->assertOk();
        $this->command($order,'accept')->assertOk()->assertJsonPath('items.0.unit_name','Box')->assertJsonPath('items.0.product_name','Coffee')
            ->assertJsonPath('items.0.contains_qty','12.000000')->assertJsonPath('items.0.price','20.0000');
    }
    public function test_public_checkout_retries_keep_one_complete_order(): void
    {
        $this->publish()->assertOk();DB::table('weekly_schedules')->insert(['business_id'=>$this->shop['b']->id,'day_of_week'=>now('Asia/Beirut')->dayOfWeek,'is_off'=>false,'open_time'=>'00:00','close_time'=>'23:59']);
        $payload=['name'=>'Web customer','phone'=>'555','request_id'=>(string)Str::uuid(),'items'=>json_encode([['id'=>MenuItem::first()->id,'quantity'=>1,'price'=>0.01]]),'order_type'=>'inside'];
        $url='/b/'.$this->shop['b']->slug.'/order';$this->post($url,$payload)->assertRedirect()->assertSessionHas('success');
        $this->post($url,$payload)->assertRedirect();$this->assertDatabaseCount('orders',1);$this->assertDatabaseCount('order_items',1);
        $this->assertSame('20.0000',(string)Order::first()->total_amount);
    }
    public function test_reviewed_cash_correction_is_explicit_revision_checked_and_idempotent(): void
    {
        $this->publish()->assertOk();$order=$this->place();
        DB::transaction(fn()=>app(PosOrderSync::class)->transition($order,'complete_external','website',1));$order->refresh();
        $this->command($order,'collect')->assertStatus(409);$id=(string)Str::uuid();
        $this->command($order,'collect_correct',$id)->assertOk()->assertJsonPath('completion_source','pos')->assertJsonPath('external_collection',false);
        $this->command($order,'collect_correct',$id)->assertOk();$this->assertDatabaseCount('pos_sync_commands',1);
    }
    public function test_refunded_orders_remain_in_history_without_counting_as_completed_money(): void
    {
        $this->publish()->assertOk();$order=$this->place();
        foreach(['accept','deliver','collect','refund'] as $action){$this->command($order,$action)->assertOk();$order->refresh();}
        $this->actingAs($this->shop['u'],'business')->get('/owner/orders/completed?from=2026-11-07&to=2026-11-07')->assertOk()
            ->assertViewHas('orders',fn($rows)=>$rows->count()===1 && $rows->first()->status==='refunded')
            ->assertViewHas('totalsByCurrency',fn($totals)=>(float)$totals->get('USD')===0.0);
    }
    public function test_event_pagination_retains_every_transition_and_uses_authenticated_sync_quota(): void
    {
        $this->publish()->assertOk();
        for($i=0;$i<10;$i++) {
            $order=$this->place();foreach(['accept','preparing','ready','deliver','collect','refund'] as $action){$this->command($order,$action)->assertOk();$order->refresh();}
        }
        $first=$this->withToken($this->shop['token'])->getJson('/api/pos/sync/orders?cursor=0')->assertOk()->assertJsonCount(50,'events')->assertJsonPath('has_more',true);
        $cursor=$first->json('cursor');
        $last=$this->withToken($this->shop['token'])->getJson('/api/pos/sync/orders?cursor='.$cursor)->assertOk()->assertJsonCount(20,'events')->assertJsonPath('has_more',false);
        $ids=array_merge(array_column($first->json('events'),'id'),array_column($last->json('events'),'id'));
        $this->assertCount(70,array_unique($ids));$this->assertSame(max($ids),$last->json('cursor'));
    }
    public function test_uncategorized_products_publish_and_invalid_pack_precision_cannot_hide_the_catalog(): void
    {
        $unit=$this->unit;$unit['category']='';$this->publish(1,[$unit])->assertOk();
        $this->assertSame('Other',MenuItem::first()->category->name);
        $unit['contains_qty']='0.0000001';$this->publish(2,[$unit])->assertUnprocessable();
        $this->assertTrue((bool)MenuItem::first()->is_available);$this->assertSame(1,(int)DB::table('pos_catalogs')->value('version'));
    }

    public function test_pos_checkout_records_final_amount_once_even_after_ready_revision_changes(): void
    {
        $this->publish()->assertOk();$order=$this->place();$original=app(PosOrderSync::class)->snapshot($order)['items'];
        $this->command($order,'accept')->assertOk();$order->refresh();$stale=$order->sync_revision;
        app(PosOrderSync::class)->transition($order,'ready','website');
        $data=['command_uuid'=>(string)Str::uuid(),'order_uuid'=>$order->sync_uuid,'expected_revision'=>$stale,'action'=>'complete_pos','pos_total'=>'18.50','pos_currency'=>'USD'];
        $response=$this->withToken($this->shop['token'])->postJson('/api/pos/sync/commands',$data)->assertOk()->assertJsonPath('status','completed')->assertJsonPath('total','18.5000')->assertJsonPath('completion_source','pos');
        $this->withToken($this->shop['token'])->postJson('/api/pos/sync/commands',$data)->assertOk()->assertExactJson($response->json());
        $this->assertSame($original,$response->json('items'));
        $this->assertSame(1,DB::table('pos_sync_commands')->where('command_uuid',$data['command_uuid'])->count());
        $data['pos_total']='19';$this->withToken($this->shop['token'])->postJson('/api/pos/sync/commands',$data)->assertStatus(409);
    }
    public function test_pos_close_cannot_override_external_completion_or_currency_and_cancel_is_idempotent(): void
    {
        $this->publish()->assertOk();$order=$this->place();
        $data=['command_uuid'=>(string)Str::uuid(),'order_uuid'=>$order->sync_uuid,'expected_revision'=>1,'action'=>'complete_pos','pos_total'=>'23','pos_currency'=>'EUR'];
        $this->withToken($this->shop['token'])->postJson('/api/pos/sync/commands',$data)->assertStatus(409);
        $data['pos_currency']='USD';$data['pos_total']='1.00001';$this->withToken($this->shop['token'])->postJson('/api/pos/sync/commands',$data)->assertStatus(422);
        app(PosOrderSync::class)->transition($order,'complete_external','website');
        $data['pos_total']='23';$this->withToken($this->shop['token'])->postJson('/api/pos/sync/commands',$data)->assertStatus(409);
        $other=$this->place();$cancel=['command_uuid'=>(string)Str::uuid(),'order_uuid'=>$other->sync_uuid,'expected_revision'=>1,'action'=>'cancel_pos'];
        $this->withToken($this->shop['token'])->postJson('/api/pos/sync/commands',$cancel)->assertOk()->assertJsonPath('status','cancelled');
        $this->withToken($this->shop['token'])->postJson('/api/pos/sync/commands',$cancel)->assertOk()->assertJsonPath('status','cancelled');
    }
}
