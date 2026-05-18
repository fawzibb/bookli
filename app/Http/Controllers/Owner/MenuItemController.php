<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

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
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $this->uploadMenuItemImage($request->file('image'), $businessId);
        }

        MenuItem::create([
            'business_id' => $businessId,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath,
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
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $data = [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'is_available' => $request->has('is_available'),
        ];

        if ($request->hasFile('image')) {
            if ($menuItem->image) {
                Storage::disk('s3')->delete($menuItem->image);
            }

            $data['image'] = $this->uploadMenuItemImage($request->file('image'), $businessId);
        }

        $menuItem->update($data);

        return back()->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $businessId = Auth::guard('business')->user()->business_id;
        abort_if($menuItem->business_id !== $businessId, 403);

        if ($menuItem->image) {
            Storage::disk('s3')->delete($menuItem->image);
        }

        $menuItem->delete();

        return back()->with('success', 'Menu item deleted successfully.');
    }

   private function uploadMenuItemImage($file, int $businessId): string
{
    $sourcePath = $file->getRealPath();
    $mime = $file->getMimeType();

    if ($mime === 'image/jpeg') {
        $sourceImage = imagecreatefromjpeg($sourcePath);
    } elseif ($mime === 'image/png') {
        $sourceImage = imagecreatefrompng($sourcePath);
        imagepalettetotruecolor($sourceImage);
        imagealphablending($sourceImage, true);
        imagesavealpha($sourceImage, true);
    } elseif ($mime === 'image/webp') {
        $sourceImage = imagecreatefromwebp($sourcePath);
    } else {
        throw new \Exception('Unsupported image type.');
    }

    $width = imagesx($sourceImage);
    $height = imagesy($sourceImage);

    $newWidth = min(900, $width);
    $newHeight = intval(($height / $width) * $newWidth);

    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

    imagecopyresampled(
        $resizedImage,
        $sourceImage,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $width,
        $height
    );

    $tempPath = tempnam(sys_get_temp_dir(), 'bookli_') . '.webp';

    imagewebp($resizedImage, $tempPath, 80);

    imagedestroy($sourceImage);
    imagedestroy($resizedImage);

    $path = 'businesses/' . $businessId . '/menu-items/' . Str::uuid() . '.webp';

    Storage::disk('s3')->put($path, file_get_contents($tempPath));

    unlink($tempPath);

    return $path;
}
}