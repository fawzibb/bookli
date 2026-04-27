<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->user()->business;

        abort_if(!$business, 404);

        $publicUrl = url('/b/' . $business->slug);

        return view('owner.qrcode.index', compact('business', 'publicUrl'));
    }
}