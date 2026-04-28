<div class="hero">
    <div class="hero-content">

        <div class="hero-text">
            <h1>{{ $business->name }}</h1>

            <p>
                {{
                    $settings->public_tagline
                    ?? (
                        $business->mode === 'booking'
                        ? __('messages.book_appointment')
                        : __('messages.browse_menu_order')
                    )
                }}
            </p>
        </div>

        @if(!empty($business->logo))
            <div class="hero-logo-wrap">
                <img src="{{ asset('storage/' . $business->logo) }}"
                     alt="{{ $business->name }}"
                     class="hero-logo">
            </div>
        @endif

    </div>
</div>