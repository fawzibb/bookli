<?php
namespace App\Services;
use App\Models\Business;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosReportReader
{
 public const COLUMNS=[
  'inventory'=>['product'=>'text','category'=>'text','unit'=>'text','stock'=>'qty','reserved'=>'qty','available'=>'qty','cost'=>'money','value'=>'money'],
  'items'=>['product'=>'text','unit'=>'text','qty'=>'qty','net'=>'money'],
  'categories'=>['category'=>'text','qty'=>'qty','net'=>'money'],
  'payment'=>['payment'=>'text','invoices'=>'count','net'=>'money'],
  'cashier'=>['cashier'=>'text','invoices'=>'count','sales'=>'money','refunds'=>'money','voids'=>'money','net'=>'money'],
  'profit'=>['product'=>'text','unit'=>'text','qty'=>'qty','net'=>'money','cost'=>'money','profit'=>'money'],
  'stock'=>['date'=>'text','product'=>'text','unit'=>'text','type'=>'text','qty'=>'qty','before'=>'qty','after'=>'qty','notes'=>'text'],
  'cash'=>['date'=>'text','type'=>'text','amount'=>'money','reference'=>'text','cashier'=>'text','notes'=>'text'],
  'reversals'=>['order_id'=>'count','type'=>'text','date'=>'text','actor'=>'text','cashier'=>'text','amount'=>'money','restocked'=>'text','notes'=>'text'],
  'review'=>['order_id'=>'count','description'=>'text']
 ];
 public static function enabledFor(Business $business):bool{return DB::table('pos_subscriptions')->where('business_id',$business->id)->whereNotNull('ends_at')->exists();}
 public static function navigationVisible():bool {$u=auth('business')->user();return $u&&$u->is_owner&&$u->is_active&&$u->business?->is_active&&self::enabledFor($u->business);}
 private static function sum(array &$row,string $key,string $value):void{$row[$key]=PosReportDecimal::add($row[$key]??'0',$value);}
 public function read(Business $business,array $filter,?callable $emit=null,bool $validateOnly=false):array {
  return DB::transaction(function()use($business,$filter,$emit,$validateOnly){
   $section=$filter['section']??'inventory';$state=DB::table('pos_report_states')->where('business_id',$business->id)->first();$batch=$state?->active_batch_id?DB::table('pos_report_batches')->where('id',$state->active_batch_id)->where('business_id',$business->id)->first():null;
   $metadata=$batch?json_decode($batch->metadata,true,512,JSON_THROW_ON_ERROR):null;$zone=$metadata['timezone']??config('pos.timezone','Asia/Beirut');$today=CarbonImmutable::now($zone)->startOfDay();
   $period=$filter['period']??'today';$to=$today;$from=match($period){'yesterday'=>$today->subDay(),'week'=>$today->startOfWeek(),'month'=>$today->startOfMonth(),'year'=>$today->startOfYear(),default=>$today};
   if($period==='yesterday')$to=$from;
   if($period==='custom'){$from=CarbonImmutable::createFromFormat('!Y-m-d',$filter['from']??$today->toDateString(),$zone);$to=CarbonImmutable::createFromFormat('!Y-m-d',$filter['to']??$today->toDateString(),$zone);}
   if($from->gt($to)||$from->diffInDays($to)>3659)throw ValidationException::withMessages(['from'=>__('pos_reports.invalid_period')]);
   $digits=$metadata['decimals']??2;$currency=$metadata['currency']??'';$page=max(1,(int)($filter['page']??1));$q=trim($filter['q']??'');$rows=[];$count=0;$summary=[];
   $matches=function(array $row)use($q){if($q==='')return true;foreach($row as $value)if(is_scalar($value)&&mb_stripos((string)$value,$q)!==false)return true;return false;};
   $push=function(array $row)use(&$rows,&$count,$page,$section,$digits,$emit,$metadata,$state){
    $formatted=[];foreach(self::COLUMNS[$section] as $key=>$kind){$v=(string)($row[$key]??'');$formatted[$key]=match($kind){'money'=>PosReportDecimal::format($v,$digits,false,$emit===null),'qty'=>PosReportDecimal::format($v,8,true,$emit===null),default=>$v};}
    $count++;if($emit)$emit($formatted,$metadata+['report_captured_at'=>$state?->captured_at]);elseif($count>($page-1)*50&&$count<=$page*50)$rows[]=$formatted;
   };
   $query=fn(string $dataset)=>DB::table('pos_report_rows')->where('batch_id',$batch?->id??'')->where('dataset',$dataset);
   $range=fn($query)=>$query->where('occurred_at','>=',$from->utc()->format('Y-m-d H:i:s'))->where('occurred_at','<',$to->addDay()->utc()->format('Y-m-d H:i:s'));
   $localDate=fn($date)=>CarbonImmutable::parse($date,'UTC')->timezone($zone)->format('Y-m-d H:i');
   if(!$validateOnly&&$batch&&in_array($section,['inventory','stock','cash','review'],true)) {
    $dataset=$section;$records=$query($dataset);if(in_array($dataset,['stock','cash'],true))$records=$range($records);
    foreach($records->lazyById(500) as $entry){$r=json_decode($entry->payload,true,512,JSON_THROW_ON_ERROR);if($entry->occurred_at)$r['date']=$localDate($entry->occurred_at);if(!$matches($r))continue;
     if($dataset==='inventory'){self::sum($summary,'stock_value',$r['value']);$r['category']=$r['category']?:__('pos_reports.uncategorized');}
     if($dataset==='cash'){self::sum($summary,$r['type']==='in'?'cash_in':'cash_out',$r['amount']);$r['type']=__('pos_reports.'.$r['type']);}
     $push($r);
    }
    if($dataset==='cash')$summary['net_cash']=PosReportDecimal::sub($summary['cash_in']??'0',$summary['cash_out']??'0');
   }
   $totals=['sales'=>'0','refunds'=>'0','voids'=>'0','net'=>'0','cost'=>'0','profit'=>'0'];
   if(!$validateOnly&&$batch&&!in_array($section,['inventory','stock','cash','review'],true)) {
    $groups=[];
    foreach($range($query('financial'))->lazyById(300) as $entry){$r=json_decode($entry->payload,true,512,JSON_THROW_ON_ERROR);$type=$r['type'];$isSale=in_array($type,['sale','legacy_sale'],true);$isReturn=in_array($type,['return','legacy_return'],true);$isVoid=$type==='void';
     self::sum($totals,'net',$r['net']);if(in_array($type,['sale','legacy_sale','amendment','amendment_reversal'],true))self::sum($totals,'sales',$r['net']);if($isReturn)self::sum($totals,'refunds',PosReportDecimal::negate($r['net']));if($isVoid)self::sum($totals,'voids',PosReportDecimal::negate($r['net']));
     foreach($r['items'] as $line)self::sum($totals,'cost',$line['cost']);
     if($section==='reversals') {
      if(!$isReturn&&!$isVoid)continue;
      $row=['order_id'=>$r['order_id'],'type'=>__('pos_reports.'.($isVoid?'voids':'refunds')),'date'=>$localDate($r['date']),'actor'=>$r['actor'],'cashier'=>$r['cashier']==='Unassigned'?__('pos_reports.unassigned'):$r['cashier'],'amount'=>PosReportDecimal::negate($r['net']),'restocked'=>__('pos_reports.'.($r['restocked']?'yes':'no')),'notes'=>$r['notes']];
      if($matches($row)){self::sum($summary,'reversed',$row['amount']);$push($row);}continue;
     }
     if(in_array($section,['cashier','payment'],true)) {
      $value=$section==='cashier'?$r['cashier']:$r['payment'];$key=$value;
      if(!isset($groups[$key]))$groups[$key]=[$section==='cashier'?'cashier':'payment'=>$value==='Unassigned'?__('pos_reports.unassigned'):$value,'sales'=>'0','refunds'=>'0','voids'=>'0','net'=>'0','invoice_ids'=>[]];
      $g=&$groups[$key];self::sum($g,'net',$r['net']);if($isSale)$g['invoice_ids'][$r['order_id']]=true;
      if(in_array($type,['sale','legacy_sale','amendment','amendment_reversal'],true))self::sum($g,'sales',$r['net']);if($isReturn)self::sum($g,'refunds',PosReportDecimal::negate($r['net']));if($isVoid)self::sum($g,'voids',PosReportDecimal::negate($r['net']));unset($g);
     }else foreach($r['items'] as $line){
      $category=$line['category']?:__('pos_reports.uncategorized');$key=$section==='categories'?$category:json_encode([$line['product_id'],$line['product'],$line['unit']],JSON_THROW_ON_ERROR);
      if(!isset($groups[$key]))$groups[$key]=['product'=>$line['product'],'category'=>$category,'unit'=>$line['unit'],'qty'=>'0','net'=>'0','cost'=>'0'];
      $g=&$groups[$key];self::sum($g,'qty',$line['base_qty']);self::sum($g,'net',$line['net']);self::sum($g,'cost',$line['cost']);unset($g);
     }
    }
    $totals['profit']=PosReportDecimal::sub($totals['net'],$totals['cost']);
    if($section!=='reversals') {
     foreach($groups as &$g){$g['invoices']=(string)count($g['invoice_ids']??[]);unset($g['invoice_ids']);if(isset($g['cost']))$g['profit']=PosReportDecimal::sub($g['net'],$g['cost']);}unset($g);
     uasort($groups,fn($a,$b)=>PosReportDecimal::compareValues($b[$section==='profit'?'profit':'net'],$a[$section==='profit'?'profit':'net']));
     foreach($groups as $r){if(in_array($section,['items','profit'],true)&&PosReportDecimal::add($r['qty'],'0')==='0'&&PosReportDecimal::add($r['net'],'0')==='0'&&($section!=='profit'||PosReportDecimal::add($r['cost'],'0')==='0'))continue;if(!$matches($r))continue;self::sum($summary,$section==='profit'?'shown_profit':'shown_net',$r[$section==='profit'?'profit':'net']);$push($r);}
    }
   }
   $summaryFormatted=[];foreach($summary as $key=>$value)$summaryFormatted[$key]=PosReportDecimal::format($value,$digits).' '.$currency;
   return ['section'=>$section,'columns'=>self::COLUMNS[$section],'rows'=>$rows,'row_count'=>$count,'page'=>$page,'from'=>$from->toDateString(),'to'=>$to->toDateString(),'period'=>$period,'q'=>$q,'metadata'=>$metadata,'currency'=>$currency,'decimals'=>$digits,'timezone'=>$zone,'summary'=>$summaryFormatted,'totals'=>$totals,'raw_summary'=>$summary,
    'captured_at'=>$state?->captured_at?CarbonImmutable::parse($state->captured_at,'UTC')->timezone($zone)->format('Y-m-d H:i:s'):null,'verified_at'=>$state?->verified_at?CarbonImmutable::parse($state->verified_at,'UTC')->timezone($zone)->format('Y-m-d H:i:s'):null,
    'stale'=>$state?->verified_at?CarbonImmutable::parse($state->verified_at,'UTC')->lt(now()->subMinutes(5)):false,'review_count'=>$metadata['counts']['review']??0];
  });
 }
}
