<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserReportController;     // ฝั่งผู้ใช้ทั่วไป
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\LineWebhookController;
use App\Http\Controllers\NotifyController;
use App\Http\Controllers\ReportImageController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/line', function () {
    return view('line');
})->name('line');

/*
|--------------------------------------------------------------------------
| Authenticated routes (ผู้ใช้ล็อกอิน)
|--------------------------------------------------------------------------
*/

// /dashboard เด้งตามบทบาท
Route::middleware('auth')->get('/dashboard', function () {
    return auth()->user()?->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('report.follow');
})->name('dashboard');

// /stock เด้งตามบทบาท
Route::middleware('auth')->get('/stock', function () {
    return auth()->user()?->role === 'admin'
        ? redirect()->route('admin.stock')
        : redirect()->route('report.follow');
})->name('stock');

// ผู้ใช้ทั่วไป: แจ้งซ่อม + ดูของตัวเอง
Route::middleware('auth')->group(function () {
    Route::get('/report',  [UserReportController::class, 'index'])->name('report.follow');
    Route::post('/report', [UserReportController::class, 'store'])->name('report.store');

    // preserve ลิงก์เดิม /report/follow (ถ้าเคยใช้)
    Route::get('/report/follow', function () {
        return redirect()->route('report.follow');
    })->name('report.follow.legacy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/report/create', [UserReportController::class, 'create'])->name('report.create');
    Route::post('/report',        [UserReportController::class, 'store'])->name('report.store');
    Route::get('/report/{report}', [UserReportController::class, 'show'])->name('report.show');

    // ลบรูปเดี่ยว
    Route::delete('/report-images/{image}', [ReportImageController::class, 'destroy'])
        ->name('report_images.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','can:manage-reports'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard แอดมิน
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/stock', [StockController::class, 'index'])->name('stock');

        // จัดการรายงานแจ้งซ่อม
        Route::get   ('/reports',                 [AdminReportController::class, 'index'])->name('reports.index');
        Route::patch ('/reports/{report}/status', [AdminReportController::class, 'updateStatus'])->name('reports.updateStatus');
        Route::delete('/reports/{report}',        [AdminReportController::class, 'destroy'])->name('reports.destroy');
        Route::get('/reports/{report}',           [AdminReportController::class, 'show'])->name('reports.show');

        // Bulk + Export
        Route::post  ('/reports/bulk-status',     [AdminReportController::class, 'bulkUpdateStatus'])->name('reports.bulkStatus');
        Route::post  ('/reports/bulk-destroy',    [AdminReportController::class, 'bulkDestroy'])->name('reports.bulkDestroy');
        Route::get   ('/reports/export',          [AdminReportController::class, 'export'])->name('reports.export');
        
        Route::delete('/report-images/{image}', [ReportImageController::class, 'destroy'])->name('report_images.destroy');

        // สต็อก
        Route::get('/stock',            [\App\Http\Controllers\Admin\StockController::class,'index'])->name('stock.index');
        Route::post('/stock',           [\App\Http\Controllers\Admin\StockController::class,'store'])->name('stock.store');
        Route::post('/stock/{stock}/in',[\App\Http\Controllers\Admin\StockController::class,'addIn'])->name('stock.in');
        Route::post('/stock/{stock}/out',[\App\Http\Controllers\Admin\StockController::class,'addOut'])->name('stock.out');
        Route::delete('/stock/{stock}', [\App\Http\Controllers\Admin\StockController::class,'destroy'])->name('stock.destroy');

        // (ถ้ามี) import/export/template
        Route::post('/stock/import',    [\App\Http\Controllers\Admin\StockController::class,'import'])->name('stock.import');
        Route::get ('/stock/export',    [\App\Http\Controllers\Admin\StockController::class,'export'])->name('stock.export');
        Route::get ('/stock/template',  [\App\Http\Controllers\Admin\StockController::class,'downloadTemplate'])->name('stock.template');

        // เตรียมพื้นที่ไว้ก่อน
        Route::view('/jobs/assign',    'admin.jobs.assign')->name('jobs.assign');
        Route::view('/borrow/staff',   'admin.borrow.staff')->name('borrow.staff');
        Route::view('/borrow/vehicle', 'admin.borrow.vehicle')->name('borrow.vehicle');
    });

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get   ('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch ('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Fallback 404 สวย ๆ (ลบได้ถ้าไม่ต้องการ)
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

require __DIR__.'/auth.php';
