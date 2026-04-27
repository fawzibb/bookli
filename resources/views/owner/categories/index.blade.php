<x-layouts.app>
<x-slot name="title">Categories</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Owner Panel</p>

<a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
<a class="nav-link active" href="{{ route('owner.categories.index') }}">{{ __('messages.categories') }}</a>
<a class="nav-link" href="{{ route('owner.menu_items.index') }}">{{ __('messages.menu_items') }}</a>
<a class="nav-link" href="{{ route('owner.orders.index') }}">{{ __('messages.orders') }}</a>
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

<div class="topbar">
    <div>
        <h1 class="page-title">{{ __('messages.categories') }}</h1>
        <p class="page-subtitle">{{ __('messages.categories_page_desc') }}</p>
    </div>
</div>

<div class="grid">

    <div class="card">
        <h3>{{ __('messages.add_category') }}</h3>

        <form method="POST" action="{{ route('owner.categories.store') }}">
            @csrf

            <div class="form-group">
                <label class="label">{{ __('messages.category_name') }}</label>
                <input class="input" type="text" name="name" required>
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.sort_order') }}</label>
                <input class="input" type="number" name="sort_order" value="0">
            </div>

            <button class="btn" type="submit">{{ __('messages.create_category') }}</button>
        </form>
    </div>

    <div class="card">
        <h3>{{ __('messages.existing_categories') }}</h3>

        @forelse($categories as $category)

            <div style="border:1px solid #e5e7eb;padding:14px;border-radius:12px;margin-bottom:12px;">

                <form method="POST" action="{{ route('owner.categories.update', $category) }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <input class="input" type="text" name="name" value="{{ $category->name }}" required>
                    </div>

                    <div class="form-group">
                        <input class="input" type="number" name="sort_order" value="{{ $category->sort_order }}">
                    </div>

                    <label>
                        <input type="checkbox" name="is_active" style="width:auto;" {{ $category->is_active ? 'checked' : '' }}>
                        {{ __('messages.active') }}
                    </label>

                    <div class="actions" style="margin-top:10px;">
                        <button class="btn" type="submit">{{ __('messages.update') }}</button>
                </form>

                <form method="POST"
                      action="{{ route('owner.categories.destroy', $category) }}"
                      onsubmit="return confirm('{{ __('messages.delete_category_confirm') }}')">
                    @csrf
                    @method('DELETE')

                    <button class="btn btn-danger" type="submit">
                        {{ __('messages.delete') }}
                    </button>
                </form>
                    </div>

            </div>

        @empty
            <p class="page-subtitle">{{ __('messages.no_categories_yet') }}</p>
        @endforelse

    </div>

</div>

</x-layouts.app>