<x-layouts.app>
<x-slot name="title">Code Usage History</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Super Admin</p>

<a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
<a class="nav-link" href="{{ route('admin.businesses.index') }}">Businesses</a>
<a class="nav-link" href="{{ route('admin.codes.index') }}">Activation Codes</a>
<a class="nav-link active" href="{{ route('admin.codes.usage') }}">Code Usage</a>
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
        <h1 class="page-title">Code Usage History</h1>
        <p class="page-subtitle">Track every redeemed activation code.</p>
    </div>
</div>

<div class="card">
    @if($usages->count())
        <div class="table-wrap">
            <table>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Business</th>
                    <th>Slug</th>
                    <th>Phone</th>
                    <th>Redeemed At</th>
                </tr>

                @foreach($usages as $index => $usage)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $usage->activationCode->code ?? '-' }}</strong></td>
                        <td>{{ $usage->business->name ?? '-' }}</td>
                        <td>
                            @if($usage->business)
                                <a href="{{ route('business.public', $usage->business->slug) }}" target="_blank">
                                    {{ $usage->business->slug }}
                                </a>
                            @endif
                        </td>
                        <td>{{ $usage->business->phone ?? '-' }}</td>
                        <td>{{ $usage->redeemed_at ? $usage->redeemed_at->format('d-m-Y H:i') : '-' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @else
        <p class="page-subtitle">No code usage found.</p>
    @endif
</div>

</x-layouts.app>