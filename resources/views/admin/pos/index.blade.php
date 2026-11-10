<x-layouts.app>
<x-slot name="title">POS subscriptions</x-slot>
<x-slot name="sidebar">
<div class="brand">Bookli</div><p>Super Admin</p>
<a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
<a class="nav-link" href="{{ route('admin.businesses.index') }}">Businesses</a>
@include('admin.partials.pos-nav')
<a class="nav-link" href="{{ route('admin.codes.index') }}">Website activation codes</a>
</x-slot>
<h1 class="page-title">POS subscriptions</h1>
<p class="page-subtitle">Independent from website subscriptions. Each code adds one year. The primary device counts toward the device limit.</p>
@if(session('success'))<div class="card" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="card" role="alert">{{ $errors->first() }}</div>@endif
@if(session('new_pos_code'))
<div class="card" role="status"><strong>New POS code — {{ session('code_business') }}</strong>
<p>Copy this code now. Its full value is shown only once.</p>
<input aria-label="New POS activation code" readonly dir="ltr" style="width:100%;font-family:monospace" value="{{ session('new_pos_code') }}"></div>
@endif
@foreach($businesses as $business)
@php($subscription = $subscriptions->get($business->id))
@php($businessDevices = $devices->get($business->id, collect()))
<section class="card" style="margin-top:20px">
<h2>{{ $business->name }}</h2>
<p>POS: <strong>{{ $subscription?->status === 'suspended' ? 'Suspended' : ($subscription?->is_valid ? 'Active' : 'Inactive / expired') }}</strong>
 · Ends: {{ $subscription?->ends_at?->timezone(config('pos.timezone'))->format('Y-m-d H:i') ?? 'Not activated' }}
 · Active devices: {{ $businessDevices->where('status', 'active')->count() }}</p>
<form method="POST" action="{{ route('admin.pos.configure', $business) }}" style="display:flex;flex-wrap:wrap;align-items:center;gap:12px">
@csrf @method('PATCH')
<label>Device limit <input name="device_limit" type="number" min="1" max="100" required value="{{ $subscription?->device_limit ?? 1 }}" style="width:90px"></label>
<label><input type="checkbox" name="suspended" value="1" @checked($subscription?->status === 'suspended')> Suspend POS</label>
<button class="btn" type="submit">Save POS settings</button>
</form>
<form method="POST" action="{{ route('admin.pos.code', $business) }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-top:16px">
@csrf <label>Code use-by date (optional) <input name="expires_at" type="date"></label>
<button class="btn" type="submit">Generate one-year POS code</button>
</form>
<details style="margin-top:16px"><summary>Devices and codes</summary>
<p>Revocation and suspension take effect at the next online verification. Existing offline licenses last until their signed deadline, at most seven days.</p>
<div class="table-wrap"><table><thead><tr><th>Device</th><th>Role</th><th>Status</th><th>Last check</th><th>Action</th></tr></thead><tbody>
@forelse($businessDevices as $device)<tr><td>{{ $device->name }}</td><td>{{ $device->role }}</td><td>{{ $device->status }}</td><td>{{ $device->last_verified_at?->timezone(config('pos.timezone'))->format('Y-m-d H:i') ?? '—' }}</td>
<td>@if($device->status === 'active')<form method="POST" action="{{ route('admin.pos.revoke', [$business, $device]) }}">@csrf<button class="btn" type="submit">Revoke</button></form>@endif</td></tr>
@empty<tr><td colspan="5">No registered devices.</td></tr>@endforelse
</tbody></table></div>
<ul>@foreach($codes->get($business->id, collect()) as $code)
<li style="margin-top:10px">{{ $code->hint }} — {{ $code->redeemed_at ? 'Used' : ($code->is_active ? 'Unused' : 'Disabled') }}
@if(!$code->redeemed_at && $code->is_active)<form method="POST" action="{{ route('admin.pos.disable', [$business, $code]) }}" style="display:inline">@csrf<button type="submit" class="btn">Disable code</button></form>@endif</li>
@endforeach</ul></details>
</section>
@endforeach
</x-layouts.app>
