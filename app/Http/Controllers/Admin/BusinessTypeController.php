<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BusinessTypeController extends Controller
{
    public function index()
    {
        $types = BusinessType::latest()->get();

        return view('admin.business-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mode' => ['required', 'string', 'max:255'],
        ]);

        BusinessType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'mode' => $request->mode,
            'is_active' => true,
        ]);

        return back()->with('success', 'Business type created successfully.');
    }

    public function update(Request $request, BusinessType $businessType)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mode' => ['required', 'string', 'max:255'],
        ]);

        $businessType->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'mode' => $request->mode,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Business type updated successfully.');
    }

    public function destroy(BusinessType $businessType)
    {
        $businessType->delete();

        return back()->with('success', 'Business type deleted successfully.');
    }
}