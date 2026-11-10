<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Hash, Storage, Event, Notification};
use Illuminate\Support\Str;
use App\Models\{Business, BusinessUser, BusinessSetting, MenuItem, Order, PosSubscription};
use App\Services\{PosLicensingService, PosOrderSync};

class PosOwnerReportsTest extends TestCase
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
        $this->withoutVite();$this->shop=$this->shop();$this->unit=['unit_uuid'=>(string)Str::uuid(),'product_uuid'=>(string)Str::uuid(),'name'=>'Coffee — Box','unit_name'=>'Box','category'=>'Drinks','description'=>'','price'=>'20.0000','contains_qty'=>'12','available_base'=>'120'];
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
    private function rows():array {
        $rows=[['dataset'=>'inventory','key'=>'1','occurred_at'=>null,'payload'=>['product'=>'Coffee','category'=>'Drinks','unit'=>'Cup','stock'=>'100.5','reserved'=>'10','available'=>'90.5','cost'=>'1.23456789','value'=>'124.074072945']]];
        foreach([[1,1,'sale','18','8','2','Alice'],[2,2,'sale','30','12','3','Bob'],[3,1,'return','-18','0','-2','Alice'],[4,3,'sale','10','4','1','Alice'],[5,3,'void','-10','-4','-1','Alice']] as [$id,$invoice,$type,$net,$cost,$qty,$cashier]) {
            $date='2026-11-07 10:00:00';$rows[]=['dataset'=>'financial','key'=>(string)$id,'occurred_at'=>$date,'payload'=>['order_id'=>$invoice,'type'=>$type,'date'=>$date,'payment'=>'Cash','actor'=>'Bob','cashier'=>$cashier,'net'=>$net,'restocked'=>$cost==='-4','notes'=>'Test','items'=>[['product_id'=>1,'product'=>'Coffee','category'=>'Drinks','unit'=>'Cup','selling_unit'=>'Cup','base_qty'=>$qty,'gross'=>$net,'net'=>$net,'cost'=>$cost]]]];
        }
        $rows[]=['dataset'=>'stock','key'=>'1','occurred_at'=>'2026-11-07 10:00:00','payload'=>['product'=>'Coffee','unit'=>'Cup','type'=>'Sale','qty'=>'-2','before'=>'100.5','after'=>'98.5','notes'=>'Movement']];
        foreach([['1','in','48'],['2','out','18']] as [$id,$type,$amount])$rows[]=['dataset'=>'cash','key'=>$id,'occurred_at'=>'2026-11-07 10:00:00','payload'=>['type'=>$type,'amount'=>$amount,'reference'=>'Invoice','notes'=>'Cash','cashier'=>'Bob']];
        $rows[]=['dataset'=>'review','key'=>'issue','occurred_at'=>null,'payload'=>['order_id'=>1,'description'=>'Historical cost review']];return $rows;
    }
    private function snapshot(int $version=1,?array $rows=null,?string $source=null):array {
        $rows=$rows??$this->rows();$counts=array_fill_keys(\App\Services\PosReportsSync::DATASETS,0);foreach($rows as $row)$counts[$row['dataset']]++;
        $chunks=[];$data=[];foreach(array_chunk($rows,4) as $i=>$part){$json=json_encode($part,JSON_THROW_ON_ERROR);$chunks[]=['index'=>$i,'sha256'=>hash('sha256',$json),'row_count'=>count($part)];$data[]=['index'=>$i,'data'=>$json];}
        return ['source_uuid'=>$source??'68bd5972-5707-4713-a594-3062ff1c95b2','version'=>$version,'content_hash'=>hash('sha256',"USD\n2\nAsia/Beirut\n".implode("\n",array_column($chunks,'sha256'))),'metadata'=>['captured_at'=>now()->toIso8601String(),'currency'=>'USD','decimals'=>2,'timezone'=>'Asia/Beirut','counts'=>$counts],'chunks'=>$chunks,'data'=>$data];
    }
    private function start(array $s):string {$body=$s;unset($body['data']);return $this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/begin',$body)->assertOk()->json('batch_uuid');}
    private function upload(array $s):string {$id=$this->start($s);foreach($s['data'] as $part)$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/chunk',['batch_uuid'=>$id]+$part)->assertOk();$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/complete',['batch_uuid'=>$id])->assertOk();return $id;}
    private function read(string $section='cashier',array $filter=[]):array{return app(\App\Services\PosReportReader::class)->read($this->shop['b'],['section'=>$section]+$filter);}
    private function owner(){return $this->actingAs($this->shop['u'],'business');}
    public function test_complete_snapshot_is_atomic_and_lost_responses_are_idempotent():void {
        $s=$this->snapshot();$id=$this->start($s);$this->assertSame($id,$this->start($s));$this->assertNull($this->read()['metadata']);
        $first=['batch_uuid'=>$id]+$s['data'][0];$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/chunk',$first)->assertOk();$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/chunk',$first)->assertOk();
        $this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/complete',['batch_uuid'=>$id])->assertStatus(409);$this->assertNull($this->read()['metadata']);
        foreach(array_slice($s['data'],1) as $part)$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/chunk',['batch_uuid'=>$id]+$part)->assertOk();
        $this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/complete',['batch_uuid'=>$id])->assertOk();$count=DB::table('pos_report_rows')->count();$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/complete',['batch_uuid'=>$id])->assertOk();$this->assertSame($count,DB::table('pos_report_rows')->count());$this->assertSame('30',$this->read()['totals']['net']);
    }
    public function test_all_reports_reconcile_damaged_refunds_voids_cost_and_original_cashier():void {
        $this->upload($this->snapshot());$rows=collect($this->read()['rows'])->keyBy('cashier');$this->assertSame('30.00',$rows['Bob']['net']);$this->assertSame('0.00',$rows['Alice']['net']);$this->assertSame('18.00',$rows['Alice']['refunds']);$this->assertSame('10.00',$rows['Alice']['voids']);$this->assertSame('2',$rows['Alice']['invoices']);
        $this->assertSame('10',$this->read('profit')['totals']['profit']);foreach(['items','categories','payment'] as $section)$this->assertSame('30.00',$this->read($section)['rows'][0]['net']);
        $this->assertSame('124.07 USD',$this->read('inventory')['summary']['stock_value']);$this->assertSame('90.5',$this->read('inventory')['rows'][0]['available']);$this->assertSame('-2',$this->read('stock')['rows'][0]['qty']);$this->assertSame('30.00 USD',$this->read('cash')['summary']['net_cash']);$this->assertCount(2,$this->read('reversals')['rows']);$this->assertSame(1,$this->read('review')['row_count']);$this->assertSame('0.00 USD',$this->read('cashier',['q'=>'Alice'])['summary']['shown_net']);
    }
    public function test_new_snapshot_cannot_expose_partial_data_or_replace_newer_history():void {
        $this->upload($this->snapshot());$older=$this->snapshot(2);$oldId=$this->start($older);$new=$this->snapshot(3);$this->upload($new);
        foreach($older['data'] as $part)$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/chunk',['batch_uuid'=>$oldId]+$part)->assertOk();$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/complete',['batch_uuid'=>$oldId])->assertStatus(409);
        $changed=$this->snapshot(4,null,(string)Str::uuid());unset($changed['data']);$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/begin',$changed)->assertStatus(409);
    }
    public function test_bad_hash_duplicate_rows_and_changed_version_replays_are_rejected():void {
        $s=$this->snapshot();$id=$this->start($s);$bad=['batch_uuid'=>$id]+$s['data'][0];$bad['data']=str_replace('Coffee','Tampered',$bad['data']);$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/chunk',$bad)->assertStatus(422);
        $changed=$s;unset($changed['data']);$changed['metadata']['currency']='EUR';$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/begin',$changed)->assertStatus(409);
        $rows=$this->rows();$rows[]=$rows[0];$dup=$this->snapshot(2,$rows);$other=$this->start($dup);foreach($dup['data'] as $i=>$part){$response=$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/chunk',['batch_uuid'=>$other]+$part);$i===count($dup['data'])-1?$response->assertStatus(422):$response->assertOk();}$this->assertNull($this->read()['metadata']);
    }
    public function test_owner_auth_tenant_boundary_and_csv_are_private():void {
        $s=$this->snapshot();$id=$this->upload($s);$this->get('/owner/reports')->assertRedirect();$this->owner()->get('/owner/reports?section=cashier')->assertOk()->assertSee('Alice');
        $this->shop['u']->forceFill(['is_owner'=>false])->save();$this->owner()->get('/owner/reports')->assertForbidden();$this->owner()->get('/owner/reports?export=csv')->assertForbidden();$this->shop['u']->forceFill(['is_owner'=>true])->save();
        $other=$this->shop();$this->withToken($other['token'])->postJson('/api/pos/sync/reports/chunk',['batch_uuid'=>$id]+$s['data'][0])->assertNotFound();$this->actingAs($other['u'],'business')->get('/owner/reports')->assertOk()->assertSee('Your reports are not synchronized yet')->assertDontSee('Coffee');
    }
    public function test_dashboard_reports_button_only_for_pos_owner():void {
        $this->owner()->get('/dashboard')->assertOk()->assertSee(route('owner.reports.index'),false);$this->shop['u']->forceFill(['is_owner'=>false])->save();$this->owner()->get('/dashboard')->assertOk()->assertDontSee(route('owner.reports.index'),false);$this->shop['u']->forceFill(['is_owner'=>true])->save();DB::table('pos_subscriptions')->where('business_id',$this->shop['b']->id)->update(['ends_at'=>null]);$this->owner()->get('/dashboard')->assertOk()->assertDontSee(route('owner.reports.index'),false);$this->owner()->get('/owner/reports')->assertForbidden();
    }
    public function test_subscription_expiry_retains_reports_and_keeps_website_pos_independent():void {
        $s=$this->snapshot();$this->upload($s);DB::table('subscriptions')->where('business_id',$this->shop['b']->id)->update(['status'=>'expired','subscription_ends_at'=>now()->subDay()]);$this->owner()->get('/owner/reports?section=cashier')->assertOk()->assertSee('Alice');DB::table('pos_subscriptions')->where('business_id',$this->shop['b']->id)->update(['ends_at'=>now()->subDay()]);$this->owner()->get('/owner/reports')->assertOk()->assertSee('Saved reports remain available');$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/heartbeat',['source_uuid'=>$s['source_uuid'],'version'=>1,'content_hash'=>$s['content_hash'],'captured_at'=>now()->toIso8601String()])->assertStatus(402);
    }
    public function test_dates_timezone_pagination_search_csv_and_xss_safety():void {
        $rows=$this->rows();$rows[1]['occurred_at']=$rows[1]['payload']['date']='2026-11-06 23:30:00';for($i=2;$i<=61;$i++){$r=$rows[0];$r['key']=(string)$i;$r['payload']['product']=$i===2?'=HYPERLINK("bad")':($i===3?'<script>alert(1)</script>':"Product $i");$rows[]=$r;}$this->upload($this->snapshot(1,$rows));$this->assertCount(50,$this->read('inventory')['rows']);$this->assertSame(61,$this->read('inventory')['row_count']);$this->assertCount(11,$this->read('inventory',['page'=>2])['rows']);$this->assertSame('58',$this->read()['totals']['sales']);
        $this->owner()->get('/owner/reports')->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;',false)->assertDontSee('<script>alert(1)</script>',false);$response=$this->owner()->get('/owner/reports?section=inventory&export=csv')->assertOk();$csv=$response->streamedContent();$this->assertStringContainsString("'=HYPERLINK",$csv);$this->assertStringContainsString('Product 61',$csv);$this->owner()->get('/owner/reports?period=custom&from=2026-11-08&to=2026-11-01')->assertSessionHasErrors('from');
    }
    public function test_heartbeat_refreshes_only_matching_complete_snapshot():void {
        $s=$this->snapshot();$this->upload($s);$this->travel(10)->minutes();$this->assertTrue($this->read()['stale']);$body=['source_uuid'=>$s['source_uuid'],'version'=>1,'content_hash'=>$s['content_hash'],'captured_at'=>now()->toIso8601String()];$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/heartbeat',$body)->assertOk();$this->assertFalse($this->read()['stale']);$body['content_hash']=str_repeat('0',64);$this->withToken($this->shop['token'])->postJson('/api/pos/sync/reports/heartbeat',$body)->assertStatus(409);
    }
    public function test_decimal_calculations_are_exact_without_float_or_optional_php_extensions():void {
        $d=\App\Services\PosReportDecimal::class;$this->assertSame('0.3',$d::add('0.1','0.2'));$this->assertSame('-0.01',$d::format('-0.005',2));$this->assertSame('1,000.00',$d::format('999.9999',2));$this->assertSame('0.00',$d::format('-0.00001',2));$this->assertSame('124.07',$d::format('124.074072945',2));$this->assertSame('100000000000000000000.00000001',$d::add('99999999999999999999.99999999','0.00000002'));
    }

    public function test_real_csharp_report_contract_matches_native_financial_totals():void {
        $fixture=json_decode(file_get_contents(base_path('tests/Fixtures/pos-report-contract.json')),true,512,JSON_THROW_ON_ERROR);$expected=$fixture['expected'];unset($fixture['expected']);$this->upload($fixture);
        $report=$this->read('cashier',['period'=>'custom','from'=>'2025-01-01','to'=>'2027-12-31']);
        foreach(['net'=>'NetSales','sales'=>'Sales','refunds'=>'Refunds','voids'=>'Voids','cost'=>'Cost','profit'=>'Profit'] as $key=>$source)$this->assertSame(\App\Services\PosReportDecimal::format((string)$expected[$source],$report['decimals']),\App\Services\PosReportDecimal::format($report['totals'][$key],$report['decimals']),$key);
        $this->assertSame($fixture['metadata']['counts']['inventory'],$this->read('inventory')['row_count']);
    }
    public function test_arabic_report_labels_render_and_never_expose_costs_publicly():void {
        $this->upload($this->snapshot());
        $en=$this->owner()->withSession(['locale'=>'en'])->get('/owner/reports?section=cashier')->assertOk();
        $ar=$this->owner()->withSession(['locale'=>'ar'])->get('/owner/reports?section=cashier')->assertOk()->assertSee('المبيعات حسب الكاشير')->assertSee('dir="rtl"',false);
        if($folder=getenv('BOOKLI_REPORT_PREVIEW_DIR')){file_put_contents($folder.'/owner-reports-preview-en.html',$en->getContent());file_put_contents($folder.'/owner-reports-preview-ar.html',$ar->getContent());}
        auth('business')->logout();$this->get('/owner/reports?section=inventory&export=csv')->assertRedirect();
    }
}
