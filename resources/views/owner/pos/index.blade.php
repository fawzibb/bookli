<x-layouts.app>
<x-slot name="title">POS subscription</x-slot>
<x-slot name="sidebar">
<div class="brand">{{ $business->name }}</div>
<a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
<a class="nav-link" href="{{ route('owner.subscription.index') }}">Website subscription</a>
<a class="nav-link active" href="{{ route('owner.pos.index') }}">POS subscription</a>
@include('owner.partials.reports-nav')
</x-slot>
<h1 class="page-title">POS subscription</h1>
<p class="page-subtitle">Your website subscription is separate. Renewing the POS does not change it.</p>
@if(session('success'))<div class="card" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="card" role="alert">{{ $errors->first() }}</div>@endif
<div class="card">
<h2>{{ $subscription?->status === 'suspended' ? 'Suspended — contact Bookli' : ($subscription?->is_valid ? 'Active' : 'Activate or renew your POS') }}</h2>
<p>Ends: {{ $subscription?->ends_at?->timezone(config('pos.timezone'))->format('Y-m-d H:i') ?? 'Not activated' }}</p>
<p>Devices: {{ $devices->where('status', 'active')->count() }} / {{ $subscription?->device_limit ?? 1 }} (including the primary device)</p>
<form method="POST" action="{{ route('owner.pos.redeem') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
@csrf <label>POS activation code <input name="code" required maxlength="100" dir="ltr" autocomplete="off" placeholder="POS-…" style="min-width:260px"></label>
<button type="submit" class="btn">Activate / add one year</button>
</form>
<p>Activation needs internet. Each code can be used once, for this business only. Early renewal adds one year after the current expiry.</p>
<p>The POS may work offline for up to seven days after successful verification, without going beyond the subscription end date.</p>
</div>
<div class="card" style="margin-top:20px"><h2>Registered devices</h2>
<p>Revoke a lost or replaced device to free its slot. Offline access already issued may continue until its signed deadline. Revoking the primary device prevents terminals from renewing until a primary is activated again.</p>
<div class="table-wrap"><table><thead><tr><th>Device</th><th>Role</th><th>Status</th><th>Last check</th><th>Action</th></tr></thead><tbody>
@forelse($devices as $device)<tr><td>{{ $device->name }}</td><td>{{ $device->role }}</td><td>{{ $device->status }}</td><td>{{ $device->last_verified_at?->timezone(config('pos.timezone'))->format('Y-m-d H:i') ?? '—' }}</td>
<td>@if($device->status === 'active')<form method="POST" action="{{ route('owner.pos.revoke', $device) }}">@csrf<button type="submit" class="btn">Revoke device</button></form>@endif</td></tr>
@empty<tr><td colspan="5">No registered devices yet.</td></tr>@endforelse
</tbody></table></div></div>
</x-layouts.app>
