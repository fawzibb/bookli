<x-layouts.app>
<x-slot name="title">Businesses</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Super Admin</p>

<a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
<a class="nav-link active" href="{{ route('admin.businesses.index') }}">Businesses</a>
<a class="nav-link" href="{{ route('admin.codes.index') }}">Activation Codes</a>
<a class="nav-link" href="{{ route('admin.codes.usage') }}">Code Usage</a>
<a class="nav-link" href="{{ route('admin.admins.index') }}">Admins</a>
<a class="nav-link" href="{{ route('admin.business-types.index') }}">Business Types</a>
<a class="nav-link" href="{{ route('admin.settings.index') }}">Settings</a>
<form method="POST" action="{{ route('admin.logout') }}" style="margin-top:18px;">
    @csrf
    <button class="btn" style="width:100%;">Logout</button>
</form>
</x-slot>

<div class="topbar">
    <div>
        <h1 class="page-title">Businesses</h1>
        <p class="page-subtitle">Manage business status and subscription periods.</p>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Type</th>
                <th>Mode</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Subscription</th>
                <th>Ends</th>
                <th>Actions</th>
            </tr>

            @foreach($businesses as $business)
                <tr>
                    <td>{{ $business->id }}</td>
                    <td>{{ $business->name }}</td>
                    <td>
                        <a href="{{ route('business.public', $business->slug) }}" target="_blank">
                            {{ $business->slug }}
                        </a>
                    </td>
                    <td>{{ $business->business_type }}</td>
                    <td>{{ $business->mode }}</td>
                    <td>{{ $business->phone }}</td>

                    <td>
                        <span class="badge {{ $business->is_active ? 'badge-completed' : 'badge-cancelled' }}">
                            {{ $business->is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </td>

                    <td>{{ $business->subscription->status ?? '-' }}</td>

                    <td>
                        @if($business->subscription?->subscription_ends_at)
                            {{ \Carbon\Carbon::parse($business->subscription->subscription_ends_at)->format('d-m-Y') }}
                        @elseif($business->subscription?->trial_ends_at)
                            {{ \Carbon\Carbon::parse($business->subscription->trial_ends_at)->format('d-m-Y') }}
                        @else
                            -
                        @endif
                    </td>

                    <td>
                       <td>
    <div class="actions" style="display:flex;flex-direction:column;gap:8px;min-width:180px;">

        <form method="POST" action="{{ route('admin.businesses.toggle', $business) }}">
            @csrf
            @method('PATCH')

            <button class="btn btn-light" type="submit" style="width:100%;">
                {{ $business->is_active ? 'Disable' : 'Enable' }}
            </button>
        </form>

        <form method="POST" action="{{ route('admin.businesses.extend', $business) }}" style="display:flex;gap:6px;">
            @csrf
            <input class="input" type="number" name="days" min="1" placeholder="+Days">
            <button class="btn" type="submit">Add</button>
        </form>

        <form method="POST" action="{{ route('admin.businesses.reduce', $business) }}" style="display:flex;gap:6px;">
            @csrf
            <input class="input" type="number" name="days" min="1" placeholder="-Days">
            <button class="btn btn-light" type="submit">Reduce</button>
        </form>

        <form method="POST" action="{{ route('admin.businesses.destroy', $business) }}"
              onsubmit="return confirm('Delete this business permanently?')">
            @csrf
            @method('DELETE')

            <button class="btn" type="submit"
                    style="width:100%;background:#dc2626;border-color:#dc2626;">
                Delete
            </button>
        </form>

    </div>
</td>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>

</x-layouts.app>