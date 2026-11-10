<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\PosLicensingService;
use Illuminate\Http\Request;
class PosController extends Controller
{
    public function activate(Request $request, PosLicensingService $service)
    {
        $data = $request->validate(['phone' => 'required|string|max:50', 'password' => 'required|string|max:255',
            'installation_id' => 'required|uuid', 'name' => 'required|string|max:100', 'role' => 'required|in:primary,terminal']);
        return response()->json($service->activate($service->owner($data['phone'], $data['password']),
            $data['installation_id'], $data['name'], $data['role']));
    }
    public function license(Request $request, PosLicensingService $service)
    {
        return response()->json($service->validateDevice($request->attributes->get('pos_device'), $request->attributes->get('pos_token_hash')));
    }
    public function redeem(Request $request, PosLicensingService $service)
    {
        $data = $request->validate(['phone' => 'required|string|max:50', 'password' => 'required|string|max:255', 'code' => 'required|string|max:100']);
        $subscription = $service->redeem($service->owner($data['phone'], $data['password']), $data['code']);
        return response()->json(['status' => $subscription->status, 'ends_at' => $subscription->ends_at->toIso8601String()]);
    }
}
