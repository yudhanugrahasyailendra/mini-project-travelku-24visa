<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\TravelKuController;
use App\Http\Controllers\TravelPackageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TravelKuController::class, 'index'])->name('travelku.index');

Route::get('/packages', [TravelPackageController::class, 'index'])->name('packages.index');
Route::post('/packages', [TravelPackageController::class, 'store'])->name('packages.store');
Route::put('/packages/{travelPackage}', [TravelPackageController::class, 'update'])->name('packages.update');
Route::delete('/packages/{travelPackage}', [TravelPackageController::class, 'destroy'])->name('packages.destroy');

Route::post('/bookings/validate', [TravelKuController::class, 'validateBooking'])->name('bookings.validate');

Route::get('/bookings/report', [BookingController::class, 'report'])->name('bookings.report');
Route::get('/bookings/export', [BookingController::class, 'export'])->name('bookings.export');
Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status');
Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
