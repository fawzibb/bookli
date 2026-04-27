<x-layouts.app>
<x-slot name="title">Business Types</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Admin Panel</p>

<a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
<a class="nav-link" href="{{ route('admin.businesses.index') }}">Businesses</a>
<a class="nav-link" href="{{ route('admin.codes.index') }}">Activation Codes</a>
<a class="nav-link" href="{{ route('admin.codes.usage') }}">Code Usage</a>
<a class="nav-link" href="{{ route('admin.admins.index') }}">Admins</a>
<a class="nav-link active" href="{{ route('admin.business-types.index') }}">Business Types</a>
<a class="nav-link" href="{{ route('admin.settings.index') }}">Settings</a>

<form method="POST" action="{{ route('admin.logout') }}" style="margin-top:18px;">
    @csrf
    <button class="btn" style="width:100%;">Logout</button>
</form>
</x-slot>

<style>
.grid-page{
    display:grid;
    grid-template-columns:360px 1fr;
    gap:20px;
}

.card-box{
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:18px;
}

.form-group{
    margin-bottom:14px;
}

.form-label{
    display:block;
    margin-bottom:6px;
    font-weight:600;
}

.form-control{
    width:100%;
    padding:12px 14px;
    border:1px solid #d1d5db;
    border-radius:12px;
    box-sizing:border-box;
}

.table-wrap{
    overflow:auto;
}

.badge{
    display:inline-block;
    padding:6px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}

.badge-mode{
    background:#eef2ff;
    color:#3730a3;
}

.badge-active{
    background:#dcfce7;
    color:#166534;
}

.badge-inactive{
    background:#fee2e2;
    color:#991b1b;
}

.actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    align-items:center;
}

.btn-green{
    background:#16a34a;
    color:#fff;
    border:none;
}

.btn-red{
    background:#dc2626;
    color:#fff;
    border:none;
}

@media(max-width:992px){
    .grid-page{
        grid-template-columns:1fr;
    }
}
</style>

<div class="topbar">
    <div>
        <h1 class="page-title">Business Types</h1>
        <p class="page-subtitle">Manage business categories and assign their mode.</p>
    </div>
</div>

@if(session('success'))
    <div class="card-box" style="margin-bottom:16px; background:#f0fdf4; color:#166534;">
        {{ session('success') }}
    </div>
@endif

<div class="grid-page">

    <div class="card-box">
        <h2 style="margin-top:0;">Add New Type</h2>

        <form method="POST" action="{{ route('admin.business-types.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Type Name</label>
                <input type="text" name="name" class="form-control" placeholder="Restaurant" required>
            </div>

            <div class="form-group">
                <label class="form-label">Mode</label>
                <input type="text" name="mode" class="form-control" placeholder="menu / booking / hybrid" required>
            </div>

            <button class="btn" style="width:100%;">Add Type</button>
        </form>
    </div>

    <div class="card-box">
        <h2 style="margin-top:0;">All Types</h2>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th style="width:340px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                        <tr>
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->slug }}</td>

                            <td>
                                <span class="badge badge-mode">
                                    {{ $type->mode }}
                                </span>
                            </td>

                            <td>
                                <span class="badge {{ $type->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $type->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>
                                <form method="POST" action="{{ route('admin.business-types.update', $type) }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="actions">
                                        <input type="text" name="name" value="{{ $type->name }}" class="form-control" style="max-width:120px;">
                                        <input type="text" name="mode" value="{{ $type->mode }}" class="form-control" style="max-width:110px;">

                                        <label style="display:flex; gap:6px; align-items:center;">
                                            <input type="checkbox" name="is_active" value="1" {{ $type->is_active ? 'checked' : '' }}>
                                            Active
                                        </label>

                                        <button class="btn btn-green">Update</button>
                                    </div>
                                </form>

                                <form method="POST"
                                      action="{{ route('admin.business-types.destroy', $type) }}"
                                      onsubmit="return confirm('Delete this type?')"
                                      style="margin-top:8px;">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-red">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; color:#6b7280;">
                                No business types found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</x-layouts.app>