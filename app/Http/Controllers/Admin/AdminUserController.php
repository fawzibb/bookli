<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = Admin::latest()->get();

        return view('admin.admins.index', compact('admins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:admins,username'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        Admin::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Admin created successfully.');
    }
    public function destroy(Admin $admin)
    {
        if (Admin::count() <= 1) {
            return back()->with('error', 'Cannot delete the last admin.');
        }

        if (auth('admin')->id() === $admin->id) {
            return back()->with('error', 'You cannot delete your own account while logged in.');
        }

        $admin->delete();

        return back()->with('success', 'Admin deleted successfully.');
    }
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth('admin')->user()->username !== 'admin') {
                abort(403);
            }

            return $next($request);
        });
    }
}