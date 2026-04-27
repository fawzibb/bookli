<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $businessId = Auth::guard('business')->user()->business_id;

        $categories = Category::where('business_id', $businessId)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('owner.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $businessId = Auth::guard('business')->user()->business_id;

        $request->validate([
            'name' => ['required', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        Category::create([
            'business_id' => $businessId,
            'name' => $request->name,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $businessId = Auth::guard('business')->user()->business_id;
        abort_if($category->business_id !== $businessId, 403);

        $request->validate([
            'name' => ['required', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $category->update([
            'name' => $request->name,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $businessId = Auth::guard('business')->user()->business_id;
        abort_if($category->business_id !== $businessId, 403);

        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}