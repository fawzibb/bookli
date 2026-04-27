<x-layouts.app>
<x-slot name="title">Settings</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Super Admin</p>

<a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
<a class="nav-link" href="{{ route('admin.businesses.index') }}">Businesses</a>
<a class="nav-link" href="{{ route('admin.codes.index') }}">Activation Codes</a>
<a class="nav-link" href="{{ route('admin.codes.usage') }}">Code Usage</a>
<a class="nav-link" href="{{ route('admin.admins.index') }}">Admins</a>
<a class="nav-link" href="{{ route('admin.business-types.index') }}">Business Types</a>
<a class="nav-link active" href="{{ route('admin.settings.index') }}">Settings</a>

<form method="POST" action="{{ route('admin.logout') }}" style="margin-top:18px;">
    @csrf
    <button class="btn" style="width:100%;">Logout</button>
</form>
</x-slot>

<div class="topbar">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Manage contact information and subscription prices.</p>
    </div>
</div>

<div class="card">
    @if(session('success'))
        <div class="alert" style="background:#dcfce7;color:#166534;margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        <h3 style="margin-top:0;">Contact Us</h3>

        <div class="grid" style="grid-template-columns:1fr 1fr;">
            <div class="form-group">
                <label class="label">Phone / WhatsApp</label>
                <input class="input" type="text" name="contact_phone"
                       value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}"
                       placeholder="+961...">
            </div>

            <div class="form-group">
                <label class="label">Email</label>
                <input class="input" type="email" name="contact_email"
                       value="{{ old('contact_email', $settings['contact_email'] ?? '') }}"
                       placeholder="support@example.com">
            </div>
        </div>

        <hr style="border:none;border-top:1px solid #e5e7eb;margin:24px 0;">

        <h3>Pricing Plans</h3>

        <div class="grid" style="grid-template-columns:repeat(3,1fr);">
            <div class="form-group">
                <label class="label">Monthly Price</label>
                <input class="input" type="number" step="0.01" name="price_monthly"
                    value="{{ old('price_monthly', $settings['price_monthly'] ?? '') }}"
                    placeholder="10">
            </div>

            <div class="form-group">
                <label class="label">6 Months Price</label>
                <input class="input" type="number" step="0.01" name="price_six_months"
                       value="{{ old('price_six_months', $settings['price_six_months'] ?? '') }}"
                       placeholder="50">
            </div>

            <div class="form-group">
                <label class="label">Yearly Price</label>
                <input class="input" type="number" step="0.01" name="price_yearly"
                       value="{{ old('price_yearly', $settings['price_yearly'] ?? '') }}"
                       placeholder="90">
            </div>
        </div>

        <button class="btn" type="submit" style="margin-top:14px;">
            Save Settings
        </button>
    </form>
</div>

</x-layouts.app>