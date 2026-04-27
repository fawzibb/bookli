<x-layouts.app>
<x-slot name="title">Admin</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Super Admin</p>

<a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
<a class="nav-link" href="{{ route('admin.businesses.index') }}">Businesses</a>
<a class="nav-link" href="{{ route('admin.codes.index') }}">Activation Codes</a>
<a class="nav-link" href="{{ route('admin.codes.usage') }}">Code Usage</a>
<a class="nav-link active" href="{{ route('admin.admins.index') }}">Admins</a>
<a class="nav-link" href="{{ route('admin.business-types.index') }}">Business Types</a>
<a class="nav-link" href="{{ route('admin.settings.index') }}">Settings</a>

<form method="POST" action="{{ route('admin.logout') }}" style="margin-top:18px;">
    @csrf
    <button class="btn" style="width:100%;">Logout</button>
</form>
</x-slot>

<div class="topbar">
    <div>
        <h1 class="page-title">Admins</h1>
        <p class="page-subtitle">Manage admin accounts.</p>
    </div>
</div>

@if(session('success'))
    <div class="card" style="margin-bottom:20px; color:#166534;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="card" style="margin-bottom:20px; color:#991b1b;">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="card" style="margin-bottom:24px;">
    <h2 style="margin-top:0;">Add New Admin</h2>

    <form method="POST" action="{{ route('admin.admins.store') }}">
        @csrf

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="{{ old('username') }}" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button class="btn" type="submit">Create Admin</button>
    </form>
</div>

<div class="card">
    <h2 style="margin-top:0;">Admin Accounts</h2>

    @if($admins->count())
        <div class="table-wrap">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>

                @foreach($admins as $admin)
                    <tr>
                        <td>{{ $admin->name }}</td>
                        <td>{{ $admin->username }}</td>
                        <td>{{ $admin->created_at->format('d-m-Y H:i') }}</td>
                        <td>
    <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}"
          onsubmit="return confirm('Delete this admin?')">
        @csrf
        @method('DELETE')

        <button class="btn btn-danger" type="submit">
            Delete
        </button>
    </form>
</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @else
        <p class="page-subtitle">No admins found.</p>
    @endif
</div>

</x-layouts.admin>