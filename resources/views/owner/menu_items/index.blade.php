<x-layouts.app>
<x-slot name="title">Menu Items</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Owner Panel</p>

<a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
<a class="nav-link" href="{{ route('owner.categories.index') }}">{{ __('messages.categories') }}</a>
<a class="nav-link active" href="{{ route('owner.menu_items.index') }}">{{ __('messages.menu_items') }}</a>
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
        <h1 class="page-title">{{ __('messages.menu_items') }}</h1>
        <p class="page-subtitle">{{ __('messages.menu_items_page_desc') }}</p>
    </div>
</div>

<div class="grid">

    <div class="card">
        <h3>{{ __('messages.add_item') }}</h3>

        <form method="POST" action="{{ route('owner.menu_items.store') }}">
            @csrf

            <div class="form-group">
                <label class="label">{{ __('messages.category') }}</label>

                <select class="select" name="category_id" required>
                    <option value="">{{ __('messages.select_category') }}</option>

                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.item_name') }}</label>
                <input class="input" type="text" name="name" required>
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.price') }}</label>
                <input class="input" type="number" step="0.01" name="price" required>
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.description') }}</label>
                <textarea class="textarea" name="description"></textarea>
            </div>

            <button class="btn" type="submit">{{ __('messages.create_item') }}</button>
        </form>
    </div>

    <div class="card">
        <h3>{{ __('messages.existing_items') }}</h3>

        @forelse($items as $item)

            <div style="border:1px solid #e5e7eb;padding:14px;border-radius:12px;margin-bottom:12px;">

                <form method="POST" action="{{ route('owner.menu_items.update', $item) }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <select class="select" name="category_id" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $item->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <input class="input" type="text" name="name" value="{{ $item->name }}" required>
                    </div>

                    <div class="form-group">
                        <input class="input" type="number" step="0.01" name="price" value="{{ $item->price }}" required>
                    </div>

                    <div class="form-group">
                        <textarea class="textarea" name="description">{{ $item->description }}</textarea>
                    </div>

                    <label>
                        <input type="checkbox" name="is_available" style="width:auto;" {{ $item->is_available ? 'checked' : '' }}>
                        {{ __('messages.available') }}
                    </label>

                    <div class="actions" style="margin-top:10px;">
                        <button class="btn" type="submit">{{ __('messages.update') }}</button>
                </form>

                <form method="POST"
                      action="{{ route('owner.menu_items.destroy', $item) }}"
                      onsubmit="return confirm('{{ __('messages.delete_item_confirm') }}')">
                    @csrf
                    @method('DELETE')

                    <button class="btn btn-danger" type="submit">
                        {{ __('messages.delete') }}
                    </button>
                </form>
                    </div>

            </div>

        @empty
            <p class="page-subtitle">{{ __('messages.no_menu_items_yet') }}</p>
        @endforelse

    </div>

</div>

</x-layouts.app>