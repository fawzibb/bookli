<x-layouts.app>
<x-slot name="title">{{ __('pos_reports.title') }}</x-slot>
<x-slot name="sidebar">
 <div class="brand">{{ $business->name }}</div>
 <a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
 @include('owner.partials.reports-nav')
 <a class="nav-link" href="{{ route('owner.pos.index') }}">{{ __('pos_reports.pos_subscription') }}</a>
</x-slot>
<div class="topbar report-heading"><div><h1 class="page-title">{{ __('pos_reports.title') }}</h1><p class="page-subtitle">{{ __('pos_reports.description') }}</p></div></div>
@if($errors->any())<div class="card report-warning" role="alert">{{ $errors->first() }}</div>@endif
@if(!$subscription?->is_valid)<div class="card report-warning" role="status">{{ __('pos_reports.expired') }} <a href="{{ route('owner.pos.index') }}">{{ __('pos_reports.pos_subscription') }}</a></div>@endif
@if(!$report['metadata'])
 <div class="card report-empty"><h2>{{ __('pos_reports.no_snapshot') }}</h2><p>{{ __('pos_reports.setup') }}</p></div>
@else
 <section class="card report-sync {{ $report['stale'] ? 'report-warning' : '' }}" aria-label="{{ __('pos_reports.last_sync') }}">
  <div><strong>{{ __('pos_reports.last_sync') }}:</strong> <bdi dir="ltr">{{ $report['captured_at'] }}</bdi> · {{ $report['timezone'] }}</div>
  <div>{{ __('pos_reports.last_contact') }}: <bdi dir="ltr">{{ $report['verified_at'] }}</bdi></div>
  @if($report['stale'])<p>{{ __('pos_reports.stale') }}</p>@endif
  @if($report['review_count'])<p>{{ __('pos_reports.review_warning',['count'=>$report['review_count']]) }} <a href="{{ route('owner.reports.index',['section'=>'review']) }}">{{ __('pos_reports.review') }}</a></p>@endif
 </section>
 <form method="GET" class="card report-filters no-print" action="{{ route('owner.reports.index') }}">
  <label>{{ __('pos_reports.report') }}<select name="section">@foreach(array_keys(\App\Services\PosReportReader::COLUMNS) as $section)<option value="{{ $section }}" @selected($report['section']===$section)>{{ __('pos_reports.'.$section) }}</option>@endforeach</select></label>
  <label>{{ __('pos_reports.period') }}<select name="period">@foreach(['today','yesterday','week','month','year','custom'] as $period)<option value="{{ $period }}" @selected($report['period']===$period)>{{ __('pos_reports.'.$period) }}</option>@endforeach</select></label>
  <label>{{ __('pos_reports.from') }}<input name="from" type="date" value="{{ $report['from'] }}"></label>
  <label>{{ __('pos_reports.to') }}<input name="to" type="date" value="{{ $report['to'] }}"></label>
  <label class="report-search">{{ __('pos_reports.search') }}<input name="q" value="{{ $report['q'] }}" maxlength="200" placeholder="{{ __('pos_reports.search_hint') }}"></label>
  <button class="btn" type="submit">{{ __('pos_reports.apply') }}</button>
 </form>
 <div class="report-toolbar no-print">
  <a class="btn" href="{{ route('owner.reports.index',array_merge(request()->except('page','export'),['export'=>'csv'])) }}">{{ __('pos_reports.export') }}</a>
  <button class="btn report-secondary" onclick="window.print()" type="button">{{ __('pos_reports.print') }}</button>
 </div>
 <section class="card report-data">
  <h2>{{ __('pos_reports.'.$report['section']) }}</h2>
  <p class="page-subtitle">{{ $report['currency'] }} · @if(in_array($report['section'],['inventory','review'])){{ __('pos_reports.current_snapshot') }}@else <bdi dir="ltr">{{ $report['from'] }} — {{ $report['to'] }}</bdi> · {{ $report['timezone'] }}@endif</p>
  @if($report['section']==='inventory')<p>{{ __('pos_reports.inventory_note') }}</p>@elseif($report['section']==='cashier')<p>{{ __('pos_reports.cashier_note') }}</p>@elseif($report['section']==='profit')<p>{{ __('pos_reports.profit_note') }}</p>@endif
  <div class="report-summary">@foreach($report['summary'] as $key=>$value)<div class="stat"><div class="stat-label">{{ __('pos_reports.'.$key) }}</div><div class="stat-value report-number" dir="ltr">{{ $value }}</div></div>@endforeach</div>
  @if(!in_array($report['section'],['inventory','stock','cash','review']))
   <p class="report-period-summary">{{ __('pos_reports.period_totals') }}:
    {{ __('pos_reports.col_sales') }} <bdi>{{ \App\Services\PosReportDecimal::format($report['totals']['sales'],$report['decimals']) }}</bdi> ·
    {{ __('pos_reports.col_refunds') }} <bdi>{{ \App\Services\PosReportDecimal::format($report['totals']['refunds'],$report['decimals']) }}</bdi> ·
    {{ __('pos_reports.col_voids') }} <bdi>{{ \App\Services\PosReportDecimal::format($report['totals']['voids'],$report['decimals']) }}</bdi> ·
    {{ __('pos_reports.col_net') }} <bdi>{{ \App\Services\PosReportDecimal::format($report['totals']['net'],$report['decimals']) }} {{ $report['currency'] }}</bdi>
   </p>
  @endif
  <p>{{ __('pos_reports.rows',['count'=>$report['row_count']]) }} · {{ __('pos_reports.filtered_totals') }}</p>
  <div class="table-wrap report-table" tabindex="0"><table><thead><tr>@foreach($report['columns'] as $key=>$type)<th scope="col">{{ __('pos_reports.col_'.$key) }}</th>@endforeach</tr></thead><tbody>
   @forelse($report['rows'] as $row)<tr>@foreach($report['columns'] as $key=>$type)<td @if($type!=='text') class="report-number" dir="ltr" @endif>{{ $row[$key] }}</td>@endforeach</tr>
   @empty<tr><td colspan="{{ count($report['columns']) }}">{{ __('pos_reports.empty') }}</td></tr>@endforelse
  </tbody></table></div>
  <nav class="report-pagination no-print" aria-label="{{ __('pos_reports.pages') }}">@if($page->previousPageUrl())<a class="btn report-secondary" href="{{ $page->previousPageUrl() }}">{{ __('pos_reports.previous') }}</a>@endif<span>{{ $page->currentPage() }} / {{ max(1,$page->lastPage()) }}</span>@if($page->nextPageUrl())<a class="btn report-secondary" href="{{ $page->nextPageUrl() }}">{{ __('pos_reports.next') }}</a>@endif</nav>
  <p class="report-footnote">{{ __('pos_reports.financial_note') }} {{ __('pos_reports.print_note') }}</p>
 </section>
@endif
<style>
.report-sync,.report-filters,.report-data,.report-empty{margin-top:18px}.report-sync{font-size:14px;line-height:1.8;border-inline-start:4px solid #2563eb}.report-warning{border-inline-start:4px solid #d97706;background:#fff7ed}.report-filters{display:flex;gap:14px;flex-wrap:wrap;align-items:end}.report-filters label{display:grid;gap:7px;min-width:140px;font-size:14px;font-weight:600}.report-filters input,.report-filters select{min-height:42px;padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;background:white;color:#0f172a;width:100%;box-sizing:border-box}.report-search{flex:1;min-width:190px!important}.report-toolbar{display:flex;gap:10px;margin:18px 0;flex-wrap:wrap}.report-secondary{background:#eef2f7!important;color:#0f172a!important}.report-summary{display:flex;gap:14px;flex-wrap:wrap;margin:18px 0}.report-summary .stat{min-width:180px;flex:1}.report-number{font-variant-numeric:tabular-nums;white-space:nowrap}.report-summary .stat-value{font-size:24px}.report-table table{width:100%;border-collapse:collapse;min-width:650px}.report-table th,.report-table td{padding:13px 14px;text-align:start;border-bottom:1px solid #e2e8f0;vertical-align:top}.report-table th{background:#f8fafc;white-space:nowrap}.report-table td{max-width:420px;overflow-wrap:anywhere}.report-table tbody tr:nth-child(even){background:#fafcff}.report-pagination{display:flex;gap:14px;align-items:center;justify-content:center;margin-top:20px}.report-footnote,.report-period-summary{color:#64748b;font-size:13px;line-height:1.7}.report-data h2{margin-top:0}.report-table:focus-visible{outline:2px solid #2563eb}
@media(max-width:650px){.report-filters label{flex:1;min-width:120px}.report-filters button{width:100%}.report-summary .stat{min-width:130px}.report-summary .stat-value{font-size:20px}.report-data{padding:14px}.report-table th,.report-table td{padding:10px}}
@media print{@page{size:A4 landscape;margin:12mm}.sidebar,.desktop-top,.mobile-top,#mobileMenu,.no-print,.overlay{display:none!important}.app,.main,.content,.main-content{display:block!important;margin:0!important;padding:0!important;width:100%!important}.card{box-shadow:none!important;break-inside:auto}.report-table{overflow:visible!important}.report-table table{min-width:0!important;font-size:10px}.report-table th,.report-table td{padding:6px}.report-table tr{break-inside:avoid}.report-summary .stat{padding:8px}.report-heading{margin:0}}
</style>
</x-layouts.app>
