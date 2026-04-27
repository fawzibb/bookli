<x-layouts.app>
<x-slot name="title">Activation Codes</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Super Admin</p>

<a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
<a class="nav-link" href="{{ route('admin.businesses.index') }}">Businesses</a>
<a class="nav-link active" href="{{ route('admin.codes.index') }}">Activation Codes</a>
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
        <h1 class="page-title">Activation Codes</h1>
        <p class="page-subtitle">Create and manage subscription activation codes.</p>
    </div>
</div>

<div class="grid">
    <div class="card">
        <h3>Create New Code</h3>

        <form method="POST" action="{{ route('admin.codes.store') }}">
            @csrf

            <div class="form-group">
                <label class="label">Code</label>
                <input class="input" type="text" name="code" required>
            </div>

            <div class="form-group">
                <label class="label">Days</label>
                <input class="input" type="number" name="days" required>
            </div>

            <div class="form-group">
                <label class="label">Max Uses</label>
                <input class="input" type="number" name="max_uses" required>
            </div>

            <div class="form-group">
                <label class="label">Expires At</label>
                <input class="input" type="date" name="expires_at">
            </div>

            <button class="btn" type="submit">Create Code</button>
        </form>
    </div>

    <div class="card">
        <h3>All Codes</h3>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Days</th>
                    <th>Used</th>
                    <th>Max</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                @foreach($codes as $code)
                    <tr>
                        <td>{{ $code->id }}</td>
                        <td><strong>{{ $code->code }}</strong></td>
                        <td>{{ $code->days }}</td>
                        <td>{{ $code->used_count }}</td>
                        <td>{{ $code->max_uses }}</td>
                        <td>{{ $code->expires_at ? \Carbon\Carbon::parse($code->expires_at)->format('d-m-Y') : '-' }}</td>
                        <td>
                            <span class="badge {{ $code->is_active ? 'badge-completed' : 'badge-cancelled' }}">
                                {{ $code->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.codes.toggle', $code) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-light" type="submit">
                                    {{ $code->is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>

</x-layouts.app>