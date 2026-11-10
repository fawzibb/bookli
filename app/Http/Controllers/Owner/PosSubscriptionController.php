<?php
namespace App\Http\Controllers\Owner;
use App\Http\Controllers\Controller;
use App\Models\{PosSubscription, PosDevice};
use App\Services\PosLicensingService;
use App\Exceptions\PosLicenseException;
use Illuminate\Http\Request;
class PosSubscriptionController extends Controller
{
    public function index()
    {
        $business = auth('business')->user()->business;
        return view('owner.pos.index', ['business' => $business,
            'subscription' => PosSubscription::where('business_id', $business->id)->first(),
            'devices' => PosDevice::where('business_id', $business->id)->latest()->get()]);
    }
    public function redeem(Request $request, PosLicensingService $service)
    {
        $data = $request->validate(['code' => 'required|string|max:100']);
        try { $service->redeem(auth('business')->user(), $data['code']); }
        catch (PosLicenseException $error) { return back()->withErrors(['code' => $error->getMessage()]); }
        return back()->with('success', 'POS subscription renewed for one year.');
    }
    public function revoke(PosDevice $device, PosLicensingService $service)
    {
        $service->revoke(auth('business')->user()->business, $device);
        return back()->with('success', 'Device revoked. It cannot renew its offline license.');
    }
}
