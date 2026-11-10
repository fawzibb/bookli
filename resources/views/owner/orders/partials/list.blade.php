@forelse($orders as $order)
    <div class="card" style="margin-bottom:20px;">

        <div class="topbar" style="margin-bottom:14px;">
            <div>
                <h3 style="margin:0 0 6px;">{{ $order->order_number }}</h3>

                <p class="page-subtitle" style="margin:0;">
                    {{ $order->customer_name ?? $order->customer->name ?? '-' }}
                    {{ $order->customer_phone ?? $order->customer->phone ?? '-' }}
                </p>
            </div>

            <div>
                @php
                    $badgeClass = match($order->status) {
                        'pending' => 'badge-pending',
                        'cancelled' => 'badge-cancelled',
                        'completed' => 'badge-completed',
                        default => 'badge-confirmed',
                    };
                @endphp

                <span class="badge {{ $badgeClass }}">
                    {{ __('messages.' . $order->status) }}
                </span>
            </div>
        </div>

        <div class="grid" style="grid-template-columns:1fr 1fr;">

            <div>
    <p>
        <strong>{{ __('messages.total') }}:</strong>
        {{ $order->pos_currency ?? 'USD' }} {{ number_format($order->total_amount, fmod($order->total_amount,1)==0?0:4) }}
    </p>

    <p>
        <strong>{{ __('messages.order_type') }}:</strong>

        @if($order->order_type === 'delivery')
            {{ __('messages.delivery_order') }}
        @else
            {{ __('messages.inside_order') }}
        @endif
    </p>

    @if($order->order_type === 'delivery')
        <p>
            <strong>{{ __('messages.delivery_address') }}:</strong>
            {{ $order->delivery_address ?: '-' }}
        </p>

        <p>
            <strong>{{ __('messages.delivery_fee') }}:</strong>
            {{ $order->pos_currency ?? 'USD' }} {{ number_format($order->delivery_fee ?? 0, 2) }}
        </p>
    @endif

    <p>
        <strong>{{ __('messages.notes') }}:</strong>
        {{ $order->notes ?: '-' }}
    </p>

    <p>
        <strong>{{ __('messages.created') }}:</strong>
        {{ $order->created_at->format('d-m-Y H:i') }}
    </p>
</div>

            <div>
                <strong>{{ __('messages.items') }}</strong>

                <ul style="margin:10px 0 0;padding-left:18px;word-break:break-word;">
                    @foreach($order->items as $item)
                        <li style="margin-bottom:6px;">
                            {{ $item->item_name }} x {{ $item->quantity }}
                            = {{ $order->pos_currency ?? 'USD' }} {{ number_format($item->total_price, fmod($item->total_price,1)==0?0:4) }}
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>

        @if($order->status==='pending')
        <form method="POST" action="{{ route('owner.orders.updateStatus',$order) }}">@csrf @method('PATCH')
        <input type="hidden" name="revision" value="{{ $order->sync_revision }}"><input type="hidden" name="status" value="confirmed">
        <button class="btn" type="submit">{{ app()->getLocale()==='ar' ? 'قبول الطلب' : 'Accept order' }}</button></form>
        @endif
        <p>{{ app()->getLocale()==='ar' ? 'الإكمال من الموقع يعني أن الطلب تم تسليمه وتحصيله خارج صندوق الكاشير.' : 'Completing here means delivered and collected outside the POS cashier drawer.' }}</p>
        <div class="actions" style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">

            <form method="POST" action="{{ route('owner.orders.updateStatus', $order) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="revision" value="{{ $order->sync_revision }}">

                <input type="hidden" name="status" value="completed">

                <button class="btn" type="submit" style="background:#166534;">
                    {{ __('messages.complete') }}
                </button>
            </form>

            <form method="POST" action="{{ route('owner.orders.updateStatus', $order) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="revision" value="{{ $order->sync_revision }}">

                <input type="hidden" name="status" value="cancelled">

                <button class="btn btn-danger" type="submit">
                    {{ __('messages.delete') }}
                </button>
            </form>

        </div>

    </div>

@empty

    <div class="card">
        <p class="page-subtitle">{{ __('messages.no_orders_yet') }}</p>
    </div>

@endforelse
