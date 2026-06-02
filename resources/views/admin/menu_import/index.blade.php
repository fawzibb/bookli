<x-layouts.app>
<x-slot name="title">Import Menu</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Super Admin</p>

<a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
<a class="nav-link" href="{{ route('admin.businesses.index') }}">Businesses</a>
<a class="nav-link" href="{{ route('admin.codes.index') }}">Activation Codes</a>
<a class="nav-link" href="{{ route('admin.codes.usage') }}">Code Usage</a>
<a class="nav-link" href="{{ route('admin.admins.index') }}">Admins</a>
<a class="nav-link" href="{{ route('admin.business-types.index') }}">Business Types</a>
<a class="nav-link" href="{{ route('admin.settings.index') }}">Settings</a>

<a class="nav-link active" href="{{ route('admin.menu-import.index') }}">
    Import Menu
</a>

<form method="POST" action="{{ route('admin.logout') }}" style="margin-top:18px;">
    @csrf
    <button class="btn" style="width:100%;">Logout</button>
</form>
</x-slot>

<div class="card">

    <div class="topbar" style="margin-bottom:20px;">
        <div>
            <h2 style="margin:0;">Import Menu</h2>

            <p class="page-subtitle" style="margin-top:6px;">
                Import categories and menu items from CSV file.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:20px;">
            <ul style="margin:0;padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.menu-import.store') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf

        <div class="grid">

            <div>
                <label>Business</label>

                <select name="business_id" class="input" required>
                    <option value="">
                        Select Business
                    </option>

                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}">
                            {{ $business->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>CSV File</label>

                <input
                    type="file"
                    name="csv_file"
                    accept=".csv"
                    required
                >
            </div>

            <div>
                <label>Import Mode</label>

                <div style="
                    margin-top:10px;
                    display:flex;
                    flex-direction:column;
                    gap:12px;
                ">
                    <label style="display:flex;align-items:center;gap:10px;">
                        <input
                            type="radio"
                            name="mode"
                            value="add_only"
                            checked
                        >

                        <span>
                            Add only new items
                        </span>
                    </label>

                    <label style="display:flex;align-items:center;gap:10px;">
                        <input
                            type="radio"
                            name="mode"
                            value="replace"
                        >

                        <span>
                            Delete current menu and import again
                        </span>
                    </label>
                </div>
            </div>

        </div>

        <div style="
            margin-top:24px;
            display:flex;
            gap:12px;
            flex-wrap:wrap;
        ">

            <a href="{{ route('admin.menu-import.sample') }}"
               class="btn">
                Download Sample CSV
            </a>

            <button type="submit" class="btn">
                Import Menu
            </button>

        </div>

    </form>

</div>

</x-layouts.app>