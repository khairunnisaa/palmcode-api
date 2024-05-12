<?php

use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\MemberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;

Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->group( function () {
    Route::apiResource('bookings', BookingController::class);
    Route::apiResource('countries', CountryController::class);
    Route::apiResource('members', MemberController::class);
});
