@if(\App\Services\PosReportReader::navigationVisible())
    <a class="nav-link {{ request()->routeIs('owner.reports.*') ? 'active' : '' }}" href="{{ route('owner.reports.index') }}" @if(request()->routeIs('owner.reports.*')) aria-current="page" @endif>{{ __('pos_reports.title') }}</a>
@endif
