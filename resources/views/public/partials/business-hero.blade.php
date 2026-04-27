<div class="hero">
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