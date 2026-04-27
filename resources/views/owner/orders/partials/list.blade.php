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
                <p><strong>{{ __('messages.total') }}:</strong> ${{ number_format($order->total_amount, 2) }}</p>
                <p><strong>{{ __('messages.notes') }}:</strong> {{ $order->notes ?: '-' }}</p>
                <p><strong>{{ __('messages.created') }}:</strong> {{ $order->created_at->format('d-m-Y H:i') }}</p>
            </div>

            <div>
                <strong>{{ __('messages.items') }}</strong>

                <ul style="margin:10px 0 0;padding-left:18px;word-break:break-word;">
                    @foreach($order->items as $item)
                        <li style="margin-bottom:6px;">
                            {{ $item->item_name }} x {{ $item->quantity }}
                            = ${{ number_format($item->total_price, 2) }}
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>

        <div class="actions" style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">

            <form method="POST" action="{{ route('owner.orders.updateStatus', $order) }}">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" value="completed">

                <button class="btn" type="submit" style="background:#166534;">
                    {{ __('messages.complete') }}
                </button>
            </form>

            <form method="POST" action="{{ route('owner.orders.updateStatus', $order) }}">
                @csrf
                @method('PATCH')

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