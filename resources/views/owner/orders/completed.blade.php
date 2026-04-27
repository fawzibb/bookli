<x-layouts.app>
<x-slot name="title">{{ __('messages.completed_orders') }}</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Owner Panel</p>

<a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
<a class="nav-link" href="{{ route('owner.categories.index') }}">{{ __('messages.categories') }}</a>
<a class="nav-link" href="{{ route('owner.menu_items.index') }}">{{ __('messages.menu_items') }}</a>
<a class="nav-link" href="{{ route('owner.orders.index') }}">{{ __('messages.orders') }}</a>
<a class="nav-link active" href="{{ route('owner.orders.completed') }}">{{ __('messages.completed_orders') }}</a>
<a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.opening_hours') }}</a>
<a class="nav-link" href="{{ route('owner.qrcode') }}">{{ __('messages.qr_code') }}</a>
<a class="nav-link" href="{{ route('owner.subscription.index') }}">{{ __('messages.subscription') }}</a>
<a class="nav-link" href="{{ route('owner.public-page.index') }}">{{ __('messages.public_page_design') }}</a>
<a class="nav-link" href="{{ route('owner.settings.index') }}">{{ __('messages.settings') }}</a>

<form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">
@csrf
<button class="btn" style="width:100%;">{{ __('messages.logout') }}</button>
</form>
</x-slot>

<style>
.completed-btn{
    background:#111827;
    color:#fff;
    border:none;
    padding:10px 16px;
    border-radius:999px;
    font-weight:700;
    text-decoration:none;
}

.filter-card{
    display:flex;
    flex-wrap:wrap;
    align-items:end;
    justify-content:space-between;
    gap:16px;
    margin-bottom:20px;
}

.filter-form{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    align-items:end;
}

.filter-group{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.filter-group label{
    font-size:14px;
    font-weight:600;
    color:#374151;
}

.filter-group input[type="date"]{
    min-width:220px;
    padding:10px 12px;
    border:1px solid #d1d5db;
    border-radius:10px;
    font:inherit;
    background:#fff;
}

.completed-title{
    margin:0;
}

.completed-subtitle{
    margin:6px 0 0;
    color:#6b7280;
}

.empty-state{
    padding:24px;
    text-align:center;
    color:#6b7280;
    background:#f9fafb;
    border-radius:12px;
}
</style>

<div class="topbar">
    <div>
        <h1 class="page-title">{{ __('messages.completed_orders') }}</h1>
        <p class="page-subtitle">{{ __('messages.completed_orders_desc') }}</p>
    </div>

    <a class="btn completed-btn" href="{{ route('owner.orders.index') }}">
        {{ __('messages.back_to_orders') }}
    </a>
</div>

<div class="card filter-card">
    <div>
        <h2 class="completed-title">
            {{ __('messages.completed_on') }}
            {{ $from }} - {{ $to }}
        </h2>

        <p class="completed-subtitle">
            {{ __('messages.completed_orders_default_today') }}
        </p>
    </div>

    <form method="GET" action="{{ route('owner.orders.completed') }}" class="filter-form">
    <div class="filter-group">
        <label for="from">{{ __('messages.from') }}</label>
        <input type="date" name="from" id="from" value="{{ $from }}">
    </div>

    <div class="filter-group">
        <label for="to">{{ __('messages.to') }}</label>
        <input type="date" name="to" id="to" value="{{ $to }}">
    </div>

    <button type="submit" class="btn">
        {{ __('messages.show') }}
    </button>
</form>
</div>

<div class="card">
    @if($orders->count())
        <div class="table-wrap">
            <table>
                <tr>
                    <th>{{ __('messages.order_number') }}</th>
                    <th>{{ __('messages.customer') }}</th>
                    <th>{{ __('messages.phone') }}</th>
                    <th>{{ __('messages.items') }}</th>
                    <th>{{ __('messages.total') }}</th>
                    <th>{{ __('messages.created') }}</th>
                    <th>{{ __('messages.status') }}</th>
                </tr>

                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->customer->name ?? '-' }}</td>
                        <td>{{ $order->customer->phone ?? '-' }}</td>

                        <td>
                            <ul style="margin:0;padding-left:18px;">
                                @foreach($order->items as $item)
                                    <li>
                                        {{ $item->item_name }} x {{ $item->quantity }}
                                    </li>
                                @endforeach
                            </ul>
                        </td>

                        <td>${{ number_format($order->total_amount, 2) }}</td>

                        <td>{{ $order->created_at->format('d-m-Y H:i') }}</td>

                        <td>
                            <span class="badge badge-completed">
                                {{ __('messages.completed') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @else
        <div class="empty-state">
            {{ __('messages.no_completed_orders_for_date') }}
        </div>
    @endif
    <div style="margin-top:18px; padding-top:16px; border-top:1px solid #e5e7eb; text-align:right;">
    <strong>{{ __('messages.total') }}:</strong>
    ${{ number_format($totalAmount, 2) }}
</div>
</div>

</x-layouts.app>