<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuItemController extends Controller
{
    public function index()
    {
        $businessId = Auth::guard('business')->user()->business_id;

        $categories = Category::where('business_id', $businessId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $items = MenuItem::with('category')
            ->where('business_id', $businessId)
            ->orderByDesc('id')
            ->get();

        return view('owner.menu_items.index', compact('categories', 'items'));
    }

    public function store(Request $request)
    {
        $businessId = Auth::guard('business')->user()->business_id;

        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable'],
        ]);

        MenuItem::create([
            'business_id' => $businessId,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'is_available' => true,
        ]);

        return back()->with('success', 'Menu item created successfully.');
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $businessId = Auth::guard('business')->user()->business_id;
        abort_if($menuItem->business_id !== $businessId, 403);

        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable'],
        ]);

        $menuItem->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'is_available' => $request->has('is_available'),
        ]);

        return back()->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $businessId = Auth::guard('business')->user()->business_id;
        abort_if($menuItem->business_id !== $businessId, 403);

        $menuItem->delete();

        return back()->with('success', 'Menu item deleted successfully.');
    }
}