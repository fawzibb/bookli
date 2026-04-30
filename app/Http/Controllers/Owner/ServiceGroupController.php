<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ServiceGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceGroupController extends Controller
{
    public function index()
    {
        $businessId = Auth::guard('business')->user()->business_id;

        $groups = ServiceGroup::where('business_id', $businessId)
            ->latest()
            ->get();

        return view('owner.service-groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $businessId = Auth::guard('business')->user()->business_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity_per_slot' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        ServiceGroup::create([
            'business_id' => $businessId,
            'name' => $data['name'],
            'capacity_per_slot' => $data['capacity_per_slot'],
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()
            ->route('owner.service-groups.index')
            ->with('success', __('messages.service_group_added'));
    }

    public function update(Request $request, ServiceGroup $serviceGroup)
    {
        $businessId = Auth::guard('business')->user()->business_id;

        abort_if($serviceGroup->business_id !== $businessId, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity_per_slot' => ['required', 'integer', 'min:1', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $serviceGroup->update([
            'name' => $data['name'],
            'capacity_per_slot' => $data['capacity_per_slot'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Service group updated successfully.');
    }

    public function destroy(ServiceGroup $serviceGroup)
    {
        $businessId = Auth::guard('business')->user()->business_id;

        abort_if($serviceGroup->business_id !== $businessId, 403);

        $serviceGroup->delete();

        return back()->with('success', 'Service group deleted successfully.');
    }
}