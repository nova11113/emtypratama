<?php

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\ShipmentController;
use Illuminate\Support\Facades\Route;

// --- FIX: REDIRECT KE LOGIN ---
// Kalau buka http://emtypratama.test langsung dilempar ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// --- AUTHENTICATION ---
Route::get('/login', [AuthWebController::class, 'form'])->name('login');
Route::post('/login', [AuthWebController::class, 'login']);
Route::get('/logout', [AuthWebController::class, 'logout'])->name('logout');

// --- SEMUA RUTE HARUS LOGIN ---
Route::middleware(['login'])->group(function () {
    
    // DASHBOARD
    // Satu saja rute dashboard utama
    Route::get('/dashboard', [OrderController::class, 'dashboard'])->name('dashboard');
    
    // Chat & Internal Request
    Route::post('/internal-request', [OrderController::class, 'kirimRequest'])->name('internal.request');
    Route::post('/internal-request/baca/{id}', [OrderController::class, 'bacaRequest'])->name('internal.baca');
    Route::get('/internal-request/history', [OrderController::class, 'riwayatRequest'])->name('internal.history');

    // --- MANAJEMEN ORDER ---
    Route::prefix('order')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('order.index');
        Route::get('/create', [OrderController::class, 'create'])->name('order.create');
        Route::post('/store', [OrderController::class, 'store'])->name('order.store');
        Route::get('/report', [OrderController::class, 'report'])->name('order.report');
        Route::get('/pengiriman/{id}/create', [OrderController::class, 'pengirimanCreate'])->name('pengiriman.create');
        Route::post('/pengiriman/store', [OrderController::class, 'pengirimanStore'])->name('pengiriman.store');
        Route::get('/surat-jalan/{id}', [OrderController::class, 'suratJalan'])->name('order.suratJalan');
        Route::get('/{id}/print-size', [OrderController::class, 'printSize'])->name('order.print_size');
        Route::get('/{id}/detail', [OrderController::class, 'detail'])->name('order.detail');
        Route::get('/{id}/edit', [OrderController::class, 'edit'])->name('order.edit');
        Route::post('/{id}/update', [OrderController::class, 'update'])->name('order.update');
    });

    // --- PROCUREMENT & INVENTORY ---
    Route::get('/procurement', [OrderController::class, 'procurementIndex'])->name('procurement.index');
    Route::post('/procurement/store', [OrderController::class, 'procurementStore'])->name('procurement.store');
    Route::post('/procurement/terima/{id}', [OrderController::class, 'terimaBahan'])->name('procurement.terima');

    Route::get('/inventory', [OrderController::class, 'inventoryIndex'])->name('inventory.index');
    Route::post('/inventory/store', [OrderController::class, 'inventoryStore'])->name('inventory.store');
    Route::post('/inventory/update/{id}', [OrderController::class, 'inventoryUpdate'])->name('inventory.update');
    Route::get('/inventory/history/{id}', [OrderController::class, 'inventoryHistory'])->name('inventory.history');
    Route::get('/inventory/all-history', [OrderController::class, 'historyBahan'])->name('inventory.all_history');

    // --- DIVISI PRODUKSI ---
    Route::post('/order-update-bulk', [OrderController::class, 'updateProgressBulk'])->name('order.bulk');
    Route::get('/cutting', [OrderController::class, 'cutting'])->name('cutting.index');
    Route::get('/sewing', [OrderController::class, 'sewing'])->name('sewing.index');
    Route::get('/finishing', [OrderController::class, 'finishing'])->name('finishing.index');
    Route::get('/qc', [OrderController::class, 'qc'])->name('qc.index');

    // --- GUDANG & LOGISTIK ---
    Route::get('/gudang', [ShipmentController::class, 'index'])->name('gudang.index');
    Route::get('/shipment-history', [OrderController::class, 'shipmentHistory'])->name('order.shipmentHistory');

    // --- MANAJEMEN KARYAWAN ---
    Route::prefix('karyawan')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('employee.index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create');
        Route::post('/store', [EmployeeController::class, 'store'])->name('employee.store');
        Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('employee.edit');
        Route::put('/{id}/update', [EmployeeController::class, 'update'])->name('employee.update');
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('employee.destroy');
    });
});