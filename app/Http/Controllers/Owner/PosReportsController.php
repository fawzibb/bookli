<?php
namespace App\Http\Controllers\Owner;
use App\Http\Controllers\Controller;
use App\Services\{PosReportReader,PosReportDecimal};
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
class PosReportsController extends Controller
{
 public function index(Request $request,PosReportReader $reports){
  $business=auth('business')->user()->business;abort_unless(PosReportReader::enabledFor($business),403);
  $data=$request->validate(['section'=>'nullable|in:'.implode(',',array_keys(PosReportReader::COLUMNS)),'period'=>'nullable|in:today,yesterday,week,month,year,custom','from'=>'nullable|date_format:Y-m-d','to'=>'nullable|date_format:Y-m-d','q'=>'nullable|string|max:200','page'=>'nullable|integer|min:1|max:100000','export'=>'nullable|in:csv']);
  if(($data['export']??null)==='csv'){
   // Validate dates and availability before starting a streamed response.
   $checked=$reports->read($business,$data,null,true);abort_unless($checked['metadata'],404);
   return response()->streamDownload(function()use($reports,$business,$data,$checked){
    $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");$columns=array_keys($checked['columns']);fputcsv($out,[...array_map(fn($key)=>__('pos_reports.col_'.$key),$columns),__('pos_reports.currency'),__('pos_reports.last_sync')]);
    $reports->read($business,$data,function($row,$meta)use($out,$columns,$checked){$values=[];foreach($columns as $key){$value=$row[$key];if(preg_match('/^[=+\-@\t\r]/',$value)&&!preg_match('/^-?\d+(?:\.\d+)?$/D',$value))$value="'".$value;$values[]=$value;}$values[]=$meta['currency'];$values[]=$meta['report_captured_at'];$values=array_map(function($value){$check=ltrim($value," \t\r\n");if((preg_match('/^[=+\-@]/',$check)||preg_match('/^[\t\r\n]/',$value))&&!preg_match('/^-?\d+(?:\.\d+)?$/D',$value))return "'".$value;return $value;},$values);fputcsv($out,$values);});fclose($out);
   },'Bookli-POS-'.($data['section']??'inventory').'-'.$checked['from'].'.csv',['Content-Type'=>'text/csv; charset=UTF-8','Cache-Control'=>'private, no-store']);
  }
  $report=$reports->read($business,$data);$page=new LengthAwarePaginator($report['rows'],$report['row_count'],50,$report['page'],['path'=>$request->url(),'query'=>$request->except('page','export')]);
  $subscription=\App\Models\PosSubscription::where('business_id',$business->id)->first();
  return response()->view('owner.reports.index',compact('business','report','page','subscription'))->header('Cache-Control','private, no-store');
 }
}
