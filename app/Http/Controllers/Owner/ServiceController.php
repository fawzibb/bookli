<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index()
    {
        $businessId = Auth::guard('business')->user()->business_id;

        $services = Service::where('business_id', $businessId)
            ->orderBy('id', 'desc')
            ->get();

        return view('owner.services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $businessId = Auth::guard('business')->user()->business_id;

        $request->validate([
            'name' => ['required', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration' => ['required', 'integer', 'min:5'],
            'description' => ['nullable'],
        ]);

        Service::create([
            'business_id' => $businessId,
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return back()->with('success', 'Service created successfully.');
    }

    public function update(Request $request, Service $service)
    {
        $businessId = Auth::guard('business')->user()->business_id;

        abort_if($service->business_id !== $businessId, 403);

        $request->validate([
            'name' => ['required', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration' => ['required', 'integer', 'min:5'],
            'description' => ['nullable'],
            'is_active' => ['nullable'],
        ]);

        $service->update([
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $businessId = Auth::guard('business')->user()->business_id;

        abort_if($service->business_id !== $businessId, 403);

        $service->delete();

        return back()->with('success', 'Service deleted successfully.');
    }
}