<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\BusinessAuthController;
use App\Http\Controllers\PublicBusinessController;
use App\Http\Controllers\Owner\BookingController as OwnerBookingController;
use App\Http\Controllers\Owner\ServiceController;
use App\Http\Controllers\Owner\ScheduleController;
use App\Http\Controllers\Owner\SubscriptionController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BusinessController as AdminBusinessController;
use App\Http\Controllers\Admin\ActivationCodeController;
use App\Http\Controllers\Admin\CodeUsageController;
use App\Http\Controllers\Owner\CategoryController;
use App\Http\Controllers\Owner\MenuItemController;
use App\Http\Controllers\Owner\OrderController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Owner\PublicPageController;
use App\Http\Controllers\Owner\QrCodeController;    
use App\Http\Controllers\Admin\BusinessTypeController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Owner\BusinessSettingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Owner\ServiceGroupController;

Route::get('/language/{locale}', function ($locale) {
    if (! in_array($locale, ['en', 'ar'])) {
        abort(404);
    }

    session(['locale' => $locale]);

    return back();
})->name('language.switch');

Route::get('/', function () {

    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }

    if (Auth::guard('business')->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');

})->name('home');
Route::get('/signup', [BusinessAuthController::class, 'showSignup'])->name('signup');
Route::post('/signup', [BusinessAuthController::class, 'signup']);

Route::get('/login', [BusinessAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [BusinessAuthController::class, 'login']);

Route::get('/dashboard', [BusinessAuthController::class, 'dashboard'])
    ->middleware(['auth:business', 'business.subscription'])
    ->name('dashboard');

Route::post('/logout', [BusinessAuthController::class, 'logout'])
    ->middleware('auth:business')
    ->name('logout');

Route::middleware('auth:business')->prefix('owner')->group(function () {
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('owner.subscription.index');
    Route::post('/subscription/redeem', [SubscriptionController::class, 'redeem'])->name('owner.subscription.redeem');
    Route::get('/settings', [BusinessSettingController::class, 'index'])
        ->name('owner.settings.index');

    Route::post('/settings', [BusinessSettingController::class, 'update'])
        ->name('owner.settings.update');

    });






Route::middleware(['auth:business', 'business.subscription'])->prefix('owner')->group(function () {
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('owner.schedules.index');
    Route::post('/schedules/weekly', [ScheduleController::class, 'updateWeekly'])->name('owner.schedules.updateWeekly');
    Route::post('/schedules/recurring', [ScheduleController::class, 'storeRecurring'])->name('owner.schedules.storeRecurring');
    Route::post('/schedules/date', [ScheduleController::class, 'storeDate'])->name('owner.schedules.storeDate');
    Route::get('/qr-code', [QrCodeController::class, 'index'])->name('owner.qrcode');
    Route::get('/services', [ServiceController::class, 'index'])->name('owner.services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('owner.services.store');
    Route::patch('/services/{service}', [ServiceController::class, 'update'])->name('owner.services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('owner.services.destroy');
    Route::get('/orders/partial', [OrderController::class, 'partial'])
        ->name('owner.orders.partial');
    Route::get('/bookings/partial', [OwnerBookingController::class, 'partial'])
    ->name('owner.bookings.partial');    
    Route::get('/bookings', [OwnerBookingController::class, 'index'])->name('owner.bookings.index');
    Route::get('/bookings/completed', [OwnerBookingController::class, 'completed'])->name('owner.bookings.completed');
    Route::patch('/bookings/{booking}/status', [OwnerBookingController::class, 'updateStatus'])->name('owner.bookings.updateStatus');
    Route::delete('/bookings/{booking}', [OwnerBookingController::class, 'destroy'])->name('owner.bookings.destroy');
    Route::delete('/schedules/blocked-times/group', [ScheduleController::class, 'destroyGroup'])
        ->name('owner.schedules.destroyGroup');

    Route::delete('/schedules/blocked-times/{blockedTime}', [ScheduleController::class, 'destroy'])
        ->name('owner.schedules.blocked-times.destroy');


    Route::resource('service-groups', ServiceGroupController::class)
        ->names('owner.service-groups');

    Route::get('/public-page', [PublicPageController::class, 'index'])->name('owner.public-page.index');
    Route::post('/public-page', [PublicPageController::class, 'update'])->name('owner.public-page.update');

    Route::get('/orders', [OrderController::class, 'index'])->name('owner.orders.index');
    Route::get('/orders/completed', [OrderController::class, 'completed'])
    ->name('owner.orders.completed');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('owner.orders.updateStatus');

    Route::get('/menu-items', [MenuItemController::class, 'index'])->name('owner.menu_items.index');
    Route::post('/menu-items', [MenuItemController::class, 'store'])->name('owner.menu_items.store');
    Route::patch('/menu-items/{menuItem}', [MenuItemController::class, 'update'])->name('owner.menu_items.update');
    Route::delete('/menu-items/{menuItem}', [MenuItemController::class, 'destroy'])->name('owner.menu_items.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('owner.categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('owner.categories.store');
    Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('owner.categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('owner.categories.destroy');

    });



Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin', [AdminAuthController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/admin/business-types', [BusinessTypeController::class, 'index'])->name('admin.business-types.index');
    Route::post('/admin/business-types', [BusinessTypeController::class, 'store'])->name('admin.business-types.store');
    Route::patch('/admin/business-types/{businessType}', [BusinessTypeController::class, 'update'])->name('admin.business-types.update');
    Route::delete('/admin/business-types/{businessType}', [BusinessTypeController::class, 'destroy'])->name('admin.business-types.destroy');
    Route::get('/admin/codes', [ActivationCodeController::class, 'index'])->name('admin.codes.index');
    Route::post('/admin/codes', [ActivationCodeController::class, 'store'])->name('admin.codes.store');
    Route::patch('/admin/codes/{activationCode}/toggle', [ActivationCodeController::class, 'toggle'])->name('admin.codes.toggle');
    Route::post('/businesses/{business}/reduce', [BusinessController::class, 'reduce'])
        ->name('admin.businesses.reduce');
    Route::get('/admin/admins', [AdminUserController::class, 'index'])
        ->name('admin.admins.index');

    Route::post('/admin/admins', [AdminUserController::class, 'store'])
        ->name('admin.admins.store');
    Route::delete('/admin/admins/{admin}', [AdminUserController::class, 'destroy'])
        ->name('admin.admins.destroy');
    Route::delete('/businesses/{business}', [BusinessController::class, 'destroy'])
        ->name('admin.businesses.destroy');
    Route::get('/admin/codes/usage', [CodeUsageController::class, 'index'])->name('admin.codes.usage');
    Route::get('/settings', [SettingController::class, 'index'])
        ->name('admin.settings.index');

    Route::post('/settings', [SettingController::class, 'update'])
        ->name('admin.settings.update');
    Route::get('/admin/businesses', [AdminBusinessController::class, 'index'])->name('admin.businesses.index');
    Route::patch('/admin/businesses/{business}/toggle', [AdminBusinessController::class, 'toggle'])->name('admin.businesses.toggle');
    Route::post('/admin/businesses/{business}/extend', [AdminBusinessController::class, 'extend'])->name('admin.businesses.extend');
});

Route::post('/b/{slug}/order', [PublicBusinessController::class, 'storeOrder']);

Route::get('/b/{slug}', [PublicBusinessController::class, 'show'])->name('business.public');
Route::post('/b/{slug}/available-slots', [PublicBusinessController::class, 'availableSlots']);
Route::post('/b/{slug}/book', [PublicBusinessController::class, 'storeBooking']);
