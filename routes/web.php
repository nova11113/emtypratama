<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\ProductionController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\ShipmentController;
use App\Http\Controllers\Web\EmployeeController;

// --- 1. REDIRECT UTAMA ---
Route::get('/', function () { return redirect()->route('login'); });

// --- 2. AUTHENTICATION ---
Route::get('/login', [AuthWebController::class, 'form'])->name('login');
Route::post('/login', [AuthWebController::class, 'login']);
Route::get('/logout', [AuthWebController::class, 'logout'])->name('logout');

// --- 3. SEMUA RUTE WAJIB LOGIN ---
Route::middleware(['login'])->group(function () {
    
    // --- DASHBOARD ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- 4. EMPLOYEE MANAGEMENT (KARYAWAN) ---
    // Dipindah ke atas agar tidak tertabrak rute Order/Produksi
    Route::prefix('karyawan')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('employee.index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create');
        Route::post('/store', [EmployeeController::class, 'store'])->name('employee.store');
        Route::get('/edit/{id}', [EmployeeController::class, 'edit'])->name('employee.edit');
        Route::post('/update/{id}', [EmployeeController::class, 'update'])->name('employee.update');
        Route::get('/delete/{id}', [EmployeeController::class, 'destroy'])->name('employee.delete');
    });

    // --- 4. ORDER MANAGEMENT ---

// Tambahkan ini agar URL /order/store lu tidak MethodNotAllowed
Route::post('/order/store', [OrderController::class, 'store'])->name('order.manual_store');

// Resource tetap ada untuk index, create, edit, dll
Route::resource('order', OrderController::class)->except(['show']);

// Rute detail dan update tetap di bawah
Route::get('/order/{id}/detail', [OrderController::class, 'detail'])->name('order.detail');
Route::post('/order/{id}/update-chart', [OrderController::class, 'updateChart']);
Route::post('/order/{id}/update-variants', [OrderController::class, 'updateVariants'])->name('order.updateVariants');

    // --- 6. DIVISI PRODUKSI ---
    Route::prefix('produksi')->group(function () {
        Route::controller(ProductionController::class)->group(function () {
            Route::get('/cutting', 'cutting')->name('cutting.index');
            Route::get('/sewing', 'sewing')->name('sewing.index');
            Route::get('/finishing', 'finishing')->name('finishing.index');
            Route::get('/qc', 'qc')->name('qc.index');
            Route::get('/report', 'report')->name('order.report');
            Route::get('/print-size/{id}', 'printSize')->name('order.print_size');
            Route::post('/update-progress', 'updateProgress')->name('production.update');
            Route::post('/bulk-update', 'updateProgress')->name('order.bulk');
        });
    });

    // --- 7. GUDANG & INVENTORY ---
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/store', [InventoryController::class, 'inventoryStore'])->name('inventory.store');
        Route::get('/history/{id}', [InventoryController::class, 'inventoryHistory'])->name('inventory.history');
        Route::post('/update/{id}', [InventoryController::class, 'inventoryUpdate'])->name('inventory.update');
        Route::post('/inventory/kurangi/{id}', [InventoryController::class, 'kurangiStok'])->name('inventory.kurangi');
        Route::get('/request', [InventoryController::class, 'requestIndex'])->name('inventory.request.index');
        Route::post('/request/store', [InventoryController::class, 'requestStore'])->name('inventory.request.store');
        Route::post('/request/approve/{id}', [InventoryController::class, 'requestApprove'])->name('inventory.request.approve');
    });

    Route::prefix('procurement')->group(function () {
        Route::get('/', [InventoryController::class, 'procurement'])->name('procurement.index');
        Route::post('/store', [InventoryController::class, 'procurementStore'])->name('procurement.store');
        Route::post('/terima/{id}', [InventoryController::class, 'terimaBahan'])->name('procurement.terima');
    });

    // --- 8. GUDANG & SHIPMENT (PENGIRIMAN) ---
    Route::controller(ShipmentController::class)->group(function () {
        Route::get('/gudang', 'index')->name('gudang.index');
        Route::get('/shipment-history', 'shipmentHistory')->name('order.shipmentHistory');
        Route::get('/order/kirim/{id}', 'create')->name('order.pengirimanCreate');
        Route::post('/shipment/store', 'store')->name('order.pengirimanStore');
        Route::post('/shipment/save', 'store')->name('pengiriman.store');
        Route::get('/surat-jalan/{id}', 'print')->name('order.suratJalan');
    });

    // --- 9. INTERNAL REQUEST ---
    Route::post('/internal-request', [DashboardController::class, 'storeRequest'])->name('internal.request');
    Route::get('/internal-request/history', [DashboardController::class, 'riwayatRequest'])->name('internal.history');

}); // <--- PENUTUP MIDDLEWARE LOGIN
