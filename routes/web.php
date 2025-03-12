<?php

use App\Http\Controllers\PaytechController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('paytech');
});


Route::resource('/paytech', PaytechController::class);
Route::post('/create-payment', [PaytechController::class, 'create'])->name('payment.create');
Route::get('/payment-success', [PaytechController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment-failed', [PaytechController::class, 'paymentFailed'])->name('payment.failed');








