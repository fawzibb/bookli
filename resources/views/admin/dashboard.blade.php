<x-layouts.app>
<x-slot name="title">Admin Dashboard</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Super Admin</p>

<a class="nav-link active" href="{{ route('admin.dashboard') }}">Dashboard</a>
<a class="nav-link" href="{{ route('admin.businesses.index') }}">Businesses</a>
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
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">Manage businesses, subscriptions, and activation codes.</p>
    </div>
</div>

<div class="grid">
    <div class="card">
        <h3>Businesses</h3>
        <p class="page-subtitle">View all registered businesses and manage access.</p>
        <a class="btn" href="{{ route('admin.businesses.index') }}">Open</a>
    </div>

    <div class="card">
        <h3>Activation Codes</h3>
        <p class="page-subtitle">Create, disable, and review subscription codes.</p>
        <a class="btn" href="{{ route('admin.codes.index') }}">Open</a>
    </div>

    <div class="card">
        <h3>Code Usage History</h3>
        <p class="page-subtitle">See which business redeemed each activation code.</p>
        <a class="btn" href="{{ route('admin.codes.usage') }}">Open</a>
    </div>
</div>

</x-layouts.app>