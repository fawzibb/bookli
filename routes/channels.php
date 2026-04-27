<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('orders.{businessId}', function ($user, $businessId) {
    return (int) $user->business_id === (int) $businessId;
});
Broadcast::channel('bookings.{businessId}', function ($user, $businessId) {
    return (int) $user->business_id === (int) $businessId;
});
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
