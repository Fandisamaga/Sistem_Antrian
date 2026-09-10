<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LayananController as AdminLayananController;
use App\Http\Controllers\Admin\OperatorController as AdminOperatorController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerServiceController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\OperatorController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/display');

Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/display', [DisplayController::class, 'index'])->name('display.index');
Route::get('/display/latest', [DisplayController::class, 'latest'])->name('display.latest');
Route::get('/display/state', [DisplayController::class, 'state'])->name('display.state');

Route::middleware(['auth', 'role:cs,admin'])->group(function () {
    Route::get('/cs', [CustomerServiceController::class, 'index'])->name('cs.index');
    Route::post('/cs/mejas/{meja}/queues', [CustomerServiceController::class, 'store'])
        ->name('cs.queues.store');
});

Route::middleware(['auth', 'role:operator,admin'])->group(function () {
    Route::get('/operator', [OperatorController::class, 'index'])->name('operator.index');
    Route::patch('/operator/queues/{queue}', [OperatorController::class, 'update'])
        ->name('operator.queues.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('layanans', AdminLayananController::class)->except(['create', 'show', 'edit']);

    Route::get('/operators', [AdminOperatorController::class, 'index'])->name('operators.index');
    Route::post('/operators', [AdminOperatorController::class, 'storeOperator'])->name('operators.store');
    Route::put('/operators/{user}', [AdminOperatorController::class, 'updateOperator'])->name('operators.update');
    Route::delete('/operators/{user}', [AdminOperatorController::class, 'destroyOperator'])->name('operators.destroy');

    Route::post('/mejas', [AdminOperatorController::class, 'storeMeja'])->name('mejas.store');
    Route::put('/mejas/{meja}', [AdminOperatorController::class, 'updateMeja'])->name('mejas.update');
    Route::delete('/mejas/{meja}', [AdminOperatorController::class, 'destroyMeja'])->name('mejas.destroy');

    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print', [AdminReportController::class, 'print'])->name('reports.print');
});
