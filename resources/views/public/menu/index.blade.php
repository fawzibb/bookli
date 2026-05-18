@extends('public.layouts.public')

@section('content')

@include('public.menu.style')

@if(!$isOpenNow)
    <div class="status closed">
        {{ __('messages.orders_not_available') }}
    </div>
@else
    <div class="status open">
        {{ __('messages.orders_available') }}
    </div>
@endif

@if($categories->count())

    <div class="menu-layout">

        <div>
            <nav class="category-nav">
                @foreach($categories as $category)
                    @if($category->menuItems->count())
                        <a href="#category-{{ $category->id }}">{{ $category->name }}</a>
                    @endif
                @endforeach
            </nav>

            @foreach($categories as $category)
                @if($category->menuItems->count())
                    <section class="menu-section" id="category-{{ $category->id }}">
                        <h2 class="menu-section-title">{{ $category->name }}</h2>

                        <div class="items-grid">
                            @foreach($category->menuItems as $item)
                                <article class="item-card">
                                    @if($item->image)
                                        <img
                                            src="{{ \Storage::disk('s3')->url($item->image) }}"
                                            alt="{{ $item->name }}"
                                            class="item-image"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="item-image item-image-placeholder">
                                            {{ strtoupper(mb_substr($item->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="item-top">
                                        <div class="item-name">{{ $item->name }}</div>
                                        <div class="item-price">${{ number_format($item->price, 2) }}</div>
                                    </div>

                                    @if(!empty($item->description))
                                        <div class="item-description">
                                            {{ $item->description }}
                                        </div>
                                    @endif

                                    <div class="item-footer">
                                        <div class="item-tag">{{ $category->name }}</div>

                                        @if($isOpenNow)
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                onclick="addToCart({{ $item->id }}, @js($item->name), {{ $item->price }})"
                                            >
                                                {{ __('messages.add_to_cart') }}
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-disabled" disabled>
                                                {{ __('messages.unavailable_now') }}
                                            </button>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        </div>

        <aside class="cart">
            <div class="cart-card">
                <div class="cart-head">
                    <h2 class="cart-title">{{ __('messages.your_cart') }}</h2>

                    <button type="button" class="cart-close" onclick="toggleCart()">
                        ×
                    </button>
                </div>

                <div id="cart-items" class="cart-items-scroll">
                    <div class="cart-empty">{{ __('messages.cart_empty') }}</div>
                </div>

                <div class="total">
                    <span>{{ __('messages.total') }}</span>
                    <span>$<span id="cart-total">0.00</span></span>
                </div>

                <form method="POST" action="/b/{{ $business->slug }}/order" id="order-form">
                    @csrf

                    <input type="hidden" name="items" id="items-input">

                    <div class="form-group">
                        <label>{{ __('messages.your_name') }}</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>{{ __('messages.phone_number') }}</label>
                        <input type="text" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label>{{ __('messages.notes') }}</label>
                        <textarea name="notes" placeholder="{{ __('messages.order_notes_placeholder') }}"></textarea>
                    </div>
                    @if(($settings->delivery_enabled ?? false))
                        <div class="form-group order-type-box">
                            <div class="form-label">{{ __('messages.order_type') }}</div>

                            <label class="order-type-option">
                                <input type="radio" name="order_type" value="inside" checked>
                                <span>{{ __('messages.inside_order') }}</span>
                            </label>

                            <label class="order-type-option">
                                <input type="radio" name="order_type" value="delivery">
                                <span>
                                    {{ __('messages.delivery_order') }}
                                    (+{{ number_format($settings->delivery_fee ?? 0, 2) }} {{ $settings->currency ?? 'USD' }})
                                </span>
                            </label>
                        </div>

                        <div class="form-group" id="deliveryAddressBox" style="display:none;">
                            <label>{{ __('messages.delivery_address') }}</label>
                            <input type="text" name="delivery_address">
                        </div>

                        <script>
                            document.querySelectorAll('input[name="order_type"]').forEach(radio => {
                                radio.addEventListener('change', function () {
                                    document.getElementById('deliveryAddressBox').style.display =
                                        this.value === 'delivery' ? 'block' : 'none';
                                });
                            });
                        </script>
                    @else
                        <input type="hidden" name="order_type" value="inside">
                    @endif

                    @if($isOpenNow)
                        <button type="submit" class="btn btn-primary submit-btn">
                            {{ __('messages.place_order') }}
                        </button>
                    @else
                        <button type="submit" class="btn btn-disabled submit-btn" disabled>
                            {{ __('messages.currently_closed') }}
                        </button>
                    @endif
                </form>
            </div>
        </aside>

    </div>

    <button type="button" class="mobile-cart-btn" onclick="toggleCart()">
        {{ __('messages.cart') }} - $<span id="mobile-cart-total">0.00</span>
    </button>

@else
    <div class="empty-box">
        {{ __('messages.no_categories_items') }}
    </div>
@endif

@endsection

@push('scripts')
@include('public.menu.script')
@endpush