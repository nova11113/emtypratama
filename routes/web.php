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
    
    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/internal-request', [DashboardController::class, 'storeRequest'])->name('internal.request');
    Route::get('/internal-request/history', [DashboardController::class, 'riwayatRequest'])->name('internal.history');

    // ORDER MANAGEMENT
    Route::resource('order', OrderController::class)->except(['show']);
    Route::post('/order/store', [OrderController::class, 'store'])->name('order.store');
    Route::get('/order/{id}/detail', [OrderController::class, 'detail'])->name('order.detail');
    Route::get('/order/{id}/edit', [OrderController::class, 'edit'])->name('order.edit');
Route::post('/order/{id}/update', [OrderController::class, 'update'])->name('order.update');
Route::post('/order/{id}/update-chart', [OrderController::class, 'updateChart']);
Route::post('/order/{id}/update-variants', [OrderController::class, 'updateVariants'])->name('order.updateVariants');

   // --- DIVISI PRODUKSI ---
    Route::prefix('produksi')->group(function () {
        Route::controller(ProductionController::class)->group(function () {
            // Halaman View (emtypratama.test/produksi/cutting, dll)
            Route::get('/cutting', 'cutting')->name('cutting.index');
            Route::get('/sewing', 'sewing')->name('sewing.index');
            Route::get('/finishing', 'finishing')->name('finishing.index');
            Route::get('/qc', 'qc')->name('qc.index');
            
            // Report & Print
            Route::get('/report', 'report')->name('order.report');
            Route::get('/print-size/{id}', 'printSize')->name('order.print_size');

            // --- PROSES UPDATE (URL: emtypratama.test/produksi/update-progress) ---
            // Ini rute tunggal yang bakal dipake semua divisi (Cutting, Sewing, dll)
            Route::post('/update-progress', 'updateProgress')->name('production.update');
            
            // Alias buat jaga-jaga kalau ada kodingan lama
            Route::post('/bulk-update', 'updateProgress')->name('order.bulk');
        });
    

});

    // GUDANG & INVENTORY
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

    // --- 5. GUDANG & SHIPMENT (PENGIRIMAN) ---
Route::controller(ShipmentController::class)->group(function () {
    // Halaman Stok Gudang Jadi (Fungsi index)
    Route::get('/gudang', 'index')->name('gudang.index');
    
    // Halaman Riwayat Pengiriman (Ganti dari 'index' ke 'shipmentHistory')
    Route::get('/shipment-history', 'shipmentHistory')->name('order.shipmentHistory');
    
    // Proses Buat & Simpan Surat Jalan
    Route::get('/order/kirim/{id}', 'create')->name('order.pengirimanCreate');
    Route::post('/shipment/store', 'store')->name('order.pengirimanStore');
    
    // Alias rute buat jaga-jaga kalau di Blade lu masih manggil pengiriman.store
    Route::post('/shipment/save', 'store')->name('pengiriman.store');
    
    // Cetak Surat Jalan (Sesuaikan nama fungsi di Controller lu, tadi lu tulis 'print')
    Route::get('/surat-jalan/{id}', 'print')->name('order.suratJalan');
});

    // KARYAWAN
    Route::resource('karyawan', EmployeeController::class)->names([
        'index' => 'employee.index',
        'create' => 'employee.create',
        'store' => 'employee.store',
        'edit' => 'employee.edit',
        'update' => 'employee.update',
        'destroy' => 'employee.destroy',
    ]);
});