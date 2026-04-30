<x-layouts.app>
<x-slot name="title">{{ __('messages.service_groups') }}</x-slot>

<x-slot name="sidebar">
    <div class="brand">Bookli</div>
    <p>Owner Panel</p>

    <a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
    <a class="nav-link" href="{{ route('owner.bookings.index') }}">{{ __('messages.bookings') }}</a>
    <a class="nav-link" href="{{ route('owner.services.index') }}">{{ __('messages.services') }}</a>
    <a class="nav-link active" href="{{ route('owner.service-groups.index') }}">{{ __('messages.service_groups') }}</a>
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

<div class="container">

    <h2>{{ __('messages.service_groups') }}</h2>
    <p class="text-muted">{{ __('messages.service_groups_desc') }}</p>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    <div class="card" style="margin-bottom:20px;">
        <form method="POST" action="{{ route('owner.service-groups.store') }}">
            @csrf

            <div class="service-group-form-grid">
                <div>
                    <label class="label">{{ __('messages.group_name') }}</label>
                    <input type="text" name="name" class="input"
                           placeholder="{{ __('messages.group_name') }}" required>
                </div>

                <div>
                    <label class="label">{{ __('messages.capacity') }}</label>
                    <input type="number" name="capacity_per_slot" class="input"
                           min="1" value="1" required>
                </div>

                <div>
                    <label class="label">{{ __('messages.status') }}</label>
                    <label style="display:flex; align-items:center; gap:6px; margin:0;">
                        <input type="checkbox" name="is_active" checked style="width:auto;">
                        {{ __('messages.active') }}
                    </label>
                </div>

                <div>
                    <label class="label">{{ __('messages.actions') }}</label>
                    <button type="submit" class="btn" style="width:auto;">
                        {{ __('messages.add') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <table class="table service-groups-table">
            <thead>
                <tr>
                    <th>{{ __('messages.group_name') }}</th>
                    <th>{{ __('messages.capacity') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse($groups as $group)
                    <tr>
                        <td data-label="{{ __('messages.group_name') }}">
                            <input form="update-group-{{ $group->id }}"
                                   type="text"
                                   name="name"
                                   class="input"
                                   value="{{ $group->name }}"
                                   required>
                        </td>

                        <td data-label="{{ __('messages.capacity') }}">
                            <input form="update-group-{{ $group->id }}"
                                   type="number"
                                   name="capacity_per_slot"
                                   class="input"
                                   value="{{ $group->capacity_per_slot }}"
                                   min="1"
                                   required>
                        </td>

                        <td data-label="{{ __('messages.status') }}">
                            <input form="update-group-{{ $group->id }}"
                                   type="checkbox"
                                   name="is_active"
                                   value="1"
                                   style="width:auto;"
                                   {{ $group->is_active ? 'checked' : '' }}>
                        </td>

                        <td data-label="{{ __('messages.actions') }}">
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                <form id="update-group-{{ $group->id }}"
                                      method="POST"
                                      action="{{ route('owner.service-groups.update', $group) }}">
                                    @csrf
                                    @method('PUT')

                                    <button type="submit" class="btn small">
                                        {{ __('messages.update') }}
                                    </button>
                                </form>

                                <form method="POST"
                                      action="{{ route('owner.service-groups.destroy', $group) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn small danger"
                                            onclick="return confirm('{{ __('messages.delete_confirm') }}')">
                                        {{ __('messages.delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center;">
                            {{ __('messages.no_groups') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<style>
.service-group-form-grid{
    display:grid;
    grid-template-columns:1fr 1fr 1fr auto;
    gap:12px;
    align-items:end;
}

@media (max-width:700px){
    .service-group-form-grid{
        grid-template-columns:1fr;
        align-items:stretch;
    }

    .service-groups-table thead{
        display:none;
    }

    .service-groups-table,
    .service-groups-table tbody,
    .service-groups-table tr,
    .service-groups-table td{
        display:block;
        width:100%;
    }

    .service-groups-table tr{
        border:1px solid #e5e7eb;
        border-radius:14px;
        padding:14px;
        margin-bottom:12px;
    }

    .service-groups-table td{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        padding:8px 0;
        border-bottom:1px solid #e5e7eb;
    }

    .service-groups-table td:last-child{
        border-bottom:0;
        align-items:flex-start;
    }

    .service-groups-table td::before{
        content:attr(data-label);
        font-weight:700;
        color:#6b7280;
        min-width:100px;
    }

    .service-groups-table td input[type="text"],
    .service-groups-table td input[type="number"]{
        max-width:180px;
    }
}
</style>

</x-layouts.app>