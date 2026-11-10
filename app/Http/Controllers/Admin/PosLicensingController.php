<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Business, PosSubscription, PosDevice, PosActivationCode};
use App\Services\PosLicensingService;
use App\Exceptions\PosLicenseException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
class PosLicensingController extends Controller
{
    public function index()
    {
        return view('admin.pos.index', ['businesses' => Business::orderBy('name')->get(),
            'subscriptions' => PosSubscription::all()->keyBy('business_id'),
            'devices' => PosDevice::latest()->get()->groupBy('business_id'),
            'codes' => PosActivationCode::latest()->get()->groupBy('business_id')]);
    }
    public function configure(Request $request, Business $business, PosLicensingService $service)
    {
        $data = $request->validate(['device_limit' => 'required|integer|min:1|max:100', 'suspended' => 'sometimes|boolean']);
        try { $service->configure($business, (int)$data['device_limit'], $request->boolean('suspended')); }
        catch (PosLicenseException $error) { return back()->withErrors(['device_limit' => $error->getMessage()]); }
        return back()->with('success', 'POS settings saved.');
    }
    public function code(Request $request, Business $business, PosLicensingService $service)
    {
        $data = $request->validate(['expires_at' => 'nullable|date|after:today']);
        $expires = empty($data['expires_at']) ? null : CarbonImmutable::parse($data['expires_at'], config('pos.timezone'))->endOfDay()->utc();
        return back()->with('new_pos_code', $service->createCode($business, $expires))->with('code_business', $business->name);
    }
    public function disable(Business $business, PosActivationCode $code)
    {
        abort_unless($code->business_id == $business->id, 404);
        $code->update(['is_active' => false]);
        return back()->with('success', 'Unused POS code disabled. Existing subscriptions are unchanged.');
    }
    public function revoke(Business $business, PosDevice $device, PosLicensingService $service)
    {
        $service->revoke($business, $device);
        return back()->with('success', 'Device revoked. Cached offline access ends at its existing deadline.');
    }
}
