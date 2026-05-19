<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\TravelKuController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TravelKuController::class, 'index'])->name('travelku.index');

Route::post('/bookings/validate', [TravelKuController::class, 'validateBooking'])->name('bookings.validate');

Route::get('/bookings/report', [BookingController::class, 'report'])->name('bookings.report');
Route::get('/bookings/export', [BookingController::class, 'export'])->name('bookings.export');
Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status');
Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
