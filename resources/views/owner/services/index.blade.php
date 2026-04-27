<x-layouts.app>
<x-slot name="title">Services</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Owner Panel</p>

<a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
<a class="nav-link" href="{{ route('owner.bookings.index') }}">{{ __('messages.bookings') }}</a>
<a class="nav-link active" href="{{ route('owner.services.index') }}">{{ __('messages.services') }}</a>
<a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.schedules') }}</a>
<a class="nav-link" href="{{ route('owner.qrcode') }}">{{ __('messages.qr_code') }}</a>
<a class="nav-link" href="{{ route('owner.subscription.index') }}">{{ __('messages.subscription') }}</a>
<a class="nav-link" href="{{ route('owner.public-page.index') }}">{{ __('messages.public_page_design') }}</a>
<a class="nav-link" href="{{ route('owner.settings.index') }}">{{ __('messages.settings') }}</a>
<form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">
@csrf
<button class="btn" style="width:100%;">{{ __('messages.logout') }}</button>
</form>
</x-slot>

<div class="topbar">
    <div>
        <h1 class="page-title">{{ __('messages.services') }}</h1>
        <p class="page-subtitle">{{ __('messages.services_page_desc') }}</p>
    </div>
</div>

<div class="grid">

    <div class="card">
        <h3>{{ __('messages.add_service') }}</h3>

        <form method="POST" action="{{ route('owner.services.store') }}">
            @csrf

            <div class="form-group">
                <label class="label">{{ __('messages.service_name') }}</label>
                <input class="input" type="text" name="name" required>
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.duration_minutes') }}</label>
                <input class="input" type="number" name="duration" required>
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.price') }}</label>
                <input class="input" type="number" step="0.01" name="price">
            </div>

            <button class="btn" type="submit">{{ __('messages.create_service') }}</button>
        </form>
    </div>

    <div class="card">
        <h3>{{ __('messages.existing_services') }}</h3>

        @foreach($services as $service)
            <div style="border:1px solid #e5e7eb;padding:14px;border-radius:12px;margin-bottom:12px;">

                <form method="POST" action="{{ route('owner.services.update', $service) }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <input class="input" type="text" name="name" value="{{ $service->name }}" required>
                    </div>

                    <div class="form-group">
                        <input class="input" type="number" name="duration_minutes" value="{{ $service->duration }}" required>
                    </div>

                    <div class="form-group">
                        <input class="input" type="number" step="0.01" name="price" value="{{ $service->price }}">
                    </div>

                    <label>
                        <input type="checkbox" name="is_active" style="width:auto;" {{ $service->is_active ? 'checked' : '' }}>
                        {{ __('messages.active') }}
                    </label>

                    <div class="actions" style="margin-top:10px;">
                        <button class="btn">{{ __('messages.update') }}</button>
                </form>

                <form method="POST" action="{{ route('owner.services.destroy', $service) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">{{ __('messages.delete') }}</button>
                </form>
                    </div>

            </div>
        @endforeach

    </div>

</div>

</x-layouts.app>