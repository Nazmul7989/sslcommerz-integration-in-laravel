<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('payment',[PaymentController::class,'payment'])->name('payment');

Route::post('success',[PaymentController::class,'success'])->name('success');
Route::post('fail',[PaymentController::class,'fail'])->name('fail');
Route::post('cancel',[PaymentController::class,'cancel'])->name('cancel');

Route::post('ipn',[PaymentController::class,'ipn'])->name('ipn');
