<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivationCodeRedemption;

class CodeUsageController extends Controller
{
    public function index()
    {
        $usages = ActivationCodeRedemption::with([
            'activationCode',
            'business'
        ])
        ->orderByDesc('redeemed_at')
        ->orderByDesc('id')
        ->get();

        return view('admin.codes.usage', compact('usages'));
    }
}