<x-layouts.app>
     <x-slot name="sidebar">
        <div class="brand">Bookli</div>
        <p>Owner Panel</p>

        <a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>

        @if(auth()->guard('business')->user()->business->mode === 'booking')
            <a class="nav-link" href="{{ route('owner.bookings.index') }}">{{ __('messages.bookings') }}</a>
            <a class="nav-link" href="{{ route('owner.services.index') }}">{{ __('messages.services') }}</a>
            <a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.schedules') }}</a>
        @else
            <a class="nav-link" href="{{ route('owner.categories.index') }}">{{ __('messages.categories') }}</a>
            <a class="nav-link" href="{{ route('owner.menu_items.index') }}">{{ __('messages.menu_items') }}</a>
            <a class="nav-link" href="{{ route('owner.orders.index') }}">{{ __('messages.orders') }}</a>
            <a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.opening_hours') }}</a>
            
        @endif
        
        <a class="nav-link active" href="{{ route('owner.qrcode') }}">{{ __('messages.qr_code') }}</a>
        <a class="nav-link" href="{{ route('owner.subscription.index') }}">{{ __('messages.subscription') }}</a>
        <a class="nav-link" href="{{ route('owner.public-page.index') }}">{{ __('messages.public_page_design') }}</a>
        <a class="nav-link" href="{{ route('owner.settings.index') }}">{{ __('messages.settings') }}</a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">
            @csrf
            <button class="btn" style="width:100%;">{{ __('messages.logout') }}</button>
        </form>
    </x-slot>

   <div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border p-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.your_qr_code') }}</h1>

        <p class="text-gray-600 mt-2">
            {{ __('messages.qr_page_desc') }}
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                {{ __('messages.business_page') }}
            </h2>

            <div class="rounded-xl border bg-gray-50 p-4 break-all text-sm text-gray-700">
                {{ $publicUrl }}
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ $publicUrl }}" target="_blank"
                   class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700">
                    {{ __('messages.open_page') }}
                </a>

                <button type="button"
                        onclick="copyUrl()"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-gray-900 text-white font-medium hover:bg-black">
                    {{ __('messages.copy_link') }}
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-6 text-center">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                {{ __('messages.scan_qr_code') }}
            </h2>

            <div id="qr-wrapper" class="inline-block bg-white p-4 rounded-2xl border">
                {!! QrCode::size(240)->margin(2)->generate($publicUrl) !!}
            </div>

            <div class="mt-5">
                <button type="button"
                        onclick="downloadQr()"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700">
                    {{ __('messages.download_qr') }}
                </button>
            </div>
        </div>

    </div>
</div>

    <script>
        function copyUrl() {
            navigator.clipboard.writeText(@js($publicUrl));
            alert('Link copied successfully.');
        }

        function downloadQr() {
            const svg = document.querySelector('#qr-wrapper svg');
            if (!svg) return;

            const serializer = new XMLSerializer();
            const source = serializer.serializeToString(svg);

            const blob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href = url;
            link.download = 'qr-code-{{ $business->slug }}.svg';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            URL.revokeObjectURL(url);
        }
    </script>
</x-layouts.app>