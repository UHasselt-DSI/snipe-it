<?php

use App\Http\Controllers\Keys;
use Illuminate\Support\Facades\Route;

/*
* Keys
 */
Route::group(['prefix' => 'keys', 'middleware' => ['auth']], function () {
    Route::get(
        '{key}/checkout',
        [Keys\KeyCheckoutController::class, 'create']
    )->name('keys.checkout.show');

    Route::post(
        '{key}/checkout',
        [Keys\KeyCheckoutController::class, 'store']
    )->name('keys.checkout.store');

    Route::get(
        '{key}/checkin',
        [Keys\KeyCheckinController::class, 'create']
    )->name('keys.checkin.show');

    Route::post(
        '{key}/checkin',
        [Keys\KeyCheckinController::class, 'store']
    )->name('keys.checkin.store');
});

Route::resource('keys', Keys\KeysController::class, [
    'middleware' => ['auth'],
]);
