<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

class MenuImportController extends Controller
{
    public function index()
    {
        $businesses = Business::orderBy('name')->get();

        return view('admin.menu_import.index', compact('businesses'));
    }
    public function sample()
{
    $csv = "category,name,description,price\n";
    $csv .= "Drinks,Pepsi,Cold drink,1.50\n";
    $csv .= "Drinks,7UP,Cold drink,1.50\n";
    $csv .= "Burgers,Classic Burger,Beef burger,8.00\n";
    $csv .= "Desserts,Chocolate Cake,Chocolate cake slice,4.50\n";

    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename=\"menu_sample.csv\"');
}

public function store(Request $request)
{
    $request->validate([
        'business_id' => 'required|exists:businesses,id',
        'csv_file' => 'required|file|mimes:csv,txt',
        'mode' => 'required|in:add_only,replace',
    ]);

    $businessId = $request->business_id;

    $createdCategories = 0;
    $createdItems = 0;
    $skippedItems = 0;

    DB::transaction(function () use (
        $request,
        $businessId,
        &$createdCategories,
        &$createdItems,
        &$skippedItems
    ) {
        if ($request->mode === 'replace') {
            MenuItem::where('business_id', $businessId)->delete();
            Category::where('business_id', $businessId)->delete();
        }

        $file = fopen($request->file('csv_file')->getRealPath(), 'r');

        $header = fgetcsv($file);
        $header = array_map('trim', $header);

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);

            $categoryName = trim($data['category'] ?? '');
            $itemName = trim($data['name'] ?? '');

            if ($categoryName === '' || $itemName === '') {
                continue;
            }

            $category = Category::firstOrCreate(
                [
                    'business_id' => $businessId,
                    'name' => $categoryName,
                ],
                [
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );

            if ($category->wasRecentlyCreated) {
                $createdCategories++;
            }

            $item = MenuItem::firstOrCreate(
                [
                    'business_id' => $businessId,
                    'category_id' => $category->id,
                    'name' => $itemName,
                ],
                [
                    'description' => $data['description'] ?? null,
                    'price' => $data['price'] ?? 0,
                    'is_available' => true,
                ]
            );

            if ($item->wasRecentlyCreated) {
                $createdItems++;
            } else {
                $skippedItems++;
            }
        }

        fclose($file);
    });

    return back()->with(
        'success',
        "Imported successfully: {$createdCategories} categories, {$createdItems} new items, {$skippedItems} skipped items."
    );
}
}