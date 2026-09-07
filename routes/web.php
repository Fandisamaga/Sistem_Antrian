<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerServiceController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\OperatorController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/display');
Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/display', [DisplayController::class, 'index'])->name('display.index');
Route::get('/display/latest', [DisplayController::class, 'latest'])->name('display.latest');
Route::middleware(['auth', 'role:cs,admin'])->group(function () { Route::get('/cs', [CustomerServiceController::class, 'index'])->name('cs.index'); Route::post('/cs/mejas/{meja}/queues', [CustomerServiceController::class, 'store'])->name('cs.queues.store'); });
Route::middleware(['auth', 'role:operator,admin'])->group(function () { Route::get('/operator', [OperatorController::class, 'index'])->name('operator.index'); Route::patch('/operator/queues/{queue}', [OperatorController::class, 'update'])->name('operator.queues.update'); });
