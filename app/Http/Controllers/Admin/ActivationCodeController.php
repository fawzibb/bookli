<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivationCode;
use Illuminate\Http\Request;

class ActivationCodeController extends Controller
{
    public function index()
    {
        $codes = ActivationCode::orderByDesc('id')->get();

        return view('admin.codes.index', compact('codes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'unique:activation_codes,code'],
            'days' => ['required', 'integer', 'min:1'],
            'max_uses' => ['required', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        ActivationCode::create([
            'code' => strtoupper($request->code),
            'days' => $request->days,
            'max_uses' => $request->max_uses,
            'used_count' => 0,
            'expires_at' => $request->expires_at,
            'is_active' => true,
        ]);

        return back()->with('success', 'Activation code created.');
    }
    

    public function toggle(ActivationCode $activationCode)
    {
        $activationCode->update([
            'is_active' => !$activationCode->is_active,
        ]);

        return back()->with('success', 'Code status updated.');
    }
}