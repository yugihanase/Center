<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\LineWebhookController;
use App\Http\Controllers\NotifyController;
use App\Http\Controllers\ReportImageController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Technician\JobController;
use App\Http\Controllers\Admin\DispatchController;
use App\Http\Controllers\Webhook\ExternalWebhookController;
use App\Http\Controllers\Admin\TechnicianController;

Route::get('/', fn () => view('welcome'))->name('welcome');
Route::get('/line', fn () => view('line'))->name('line');

/*
|--------------------------------------------------------------------------
| Authenticated routes (ผู้ใช้ล็อกอิน)
|--------------------------------------------------------------------------
*/

// /dashboard เด้งตามบทบาท
Route::middleware('auth')->get('/dashboard', function () {
    $role = auth()->user()?->role;

    return match ($role) {
        'admin'      => redirect()->route('admin.dashboard'),
        'technician' => redirect()->route('tech.jobs.index'),
        default      => redirect()->route('report.follow'),
    };
})->name('dashboard');

// /stock เด้งตามบทบาท
Route::middleware('auth')->get('/stock', function () {
    return auth()->user()?->role === 'admin'
        ? redirect()->route('admin.stock')
        : redirect()->route('report.follow');
})->name('stock');

// ผู้ใช้ทั่วไป: แจ้งซ่อม + ดูของตัวเอง
Route::middleware('auth')->group(function () {
    // follow + สร้าง + บันทึก + ดูรายละเอียด
    Route::get   ('/report',            [UserReportController::class, 'index'])->name('report.follow');
    Route::get   ('/report/create',     [UserReportController::class, 'create'])->name('report.create');
    Route::post  ('/report',            [UserReportController::class, 'store'])->name('report.store');
    Route::get   ('/report/{report}',   [UserReportController::class, 'show'])->name('report.show');
    Route::patch('/report/{report}/detail', [UserReportController::class, 'updateDetail'])->name('report.updateDetail');
    Route::patch('/report/{report}/cancel', [UserReportController::class, 'cancel'])->name('report.cancel');
    Route::post('/report/{report}/images', [ReportImageController::class, 'store'])->name('report.images.store');
    Route::delete('/report/images/{image}', [ReportImageController::class, 'destroy'])
        ->name('report.images.destroy');

    // preserve ลิงก์เดิม /report/follow (ถ้าเคยใช้)
    Route::get('/report/follow', fn () => redirect()->route('report.follow'))->name('report.follow.legacy');
});

Route::post('/webhooks/external', [ExternalWebhookController::class, 'handle'])
    ->name('webhooks.external'); // พิจารณาเพิ่ม ->middleware('throttle:60,1')

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web','can:manage-reports'])
    ->prefix('admin')->name('admin.')->group(function () {

    // Dashboard แอดมิน
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // จัดการรายงานแจ้งซ่อม
    Route::get   ('/reports',                 [AdminReportController::class, 'index'])->name('reports.index');
    Route::patch ('/reports/{report}/status', [AdminReportController::class, 'updateStatus'])->name('reports.updateStatus');
    Route::delete('/reports/{report}',        [AdminReportController::class, 'destroy'])->name('reports.destroy');
    Route::get   ('/reports/{report}',        [AdminReportController::class, 'show'])->name('reports.show');

    // Bulk + Export
    Route::post  ('/reports/bulk-status',  [AdminReportController::class, 'bulkUpdateStatus'])->name('reports.bulkStatus');
    Route::post  ('/reports/bulk-destroy', [AdminReportController::class, 'bulkDestroy'])->name('reports.bulkDestroy');
    Route::get   ('/reports/export',       [AdminReportController::class, 'export'])->name('reports.export');

    // สต็อก
    Route::get   ('/stock',            [StockController::class,'index'])->name('stock');
    Route::post  ('/stock',            [StockController::class,'store'])->name('stock.store');
    Route::post  ('/stock/{stock}/in', [StockController::class,'addIn'])->name('stock.in');
    Route::post  ('/stock/{stock}/out',[StockController::class,'addOut'])->name('stock.out');
    Route::delete('/stock/{stock}',    [StockController::class,'destroy'])->name('stock.destroy');

    // import/export/template
    Route::post('/stock/import',   [StockController::class,'import'])->name('stock.import');
    Route::get ('/stock/export',   [StockController::class,'export'])->name('stock.export');
    Route::get ('/stock/template', [StockController::class,'downloadTemplate'])->name('stock.template');

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class,'index'])->name('activity_logs');

    // ===== Technicians (ช่าง/หัวหน้าช่าง) =====
    Route::get   ('/technicians',                    [TechnicianController::class, 'index'])->name('technicians.index');
    Route::post  ('/technicians',                    [TechnicianController::class, 'store'])->name('technicians.store');
    Route::patch ('/technicians/{technician}',       [TechnicianController::class, 'update'])->name('technicians.update');
    Route::delete('/technicians/{technician}',       [TechnicianController::class, 'destroy'])->name('technicians.destroy');

    Route::patch ('/technicians/{technician}/toggle',[TechnicianController::class, 'toggle'])->name('technicians.toggle');
    Route::patch ('/technicians/{technician}/promote',[TechnicianController::class, 'promote'])->name('technicians.promote');
    Route::patch ('/technicians/{technician}/demote', [TechnicianController::class, 'demote'])->name('technicians.demote');
    // ============================================

    /*
    |-------------------------------
    | ศูนย์จ่ายงาน (Dispatch)
    |-------------------------------
    */
    // หน้า assign (แสดงคิว + รายชื่อช่าง)
    Route::get('/jobs/assign', [DispatchController::class,'index'])->name('jobs.assign');

    // การกระทำเกี่ยวกับการมอบหมาย (ต้องมี gate: dispatch-jobs)
    Route::middleware('can:dispatch-jobs')->group(function () {
        Route::post('/jobs/assign',   [DispatchController::class,'assign'])->name('jobs.assign.store');
        Route::post('/jobs/reassign', [DispatchController::class,'reassign'])->name('jobs.reassign');
        Route::post('/jobs/status',   [DispatchController::class,'changeStatus'])->name('jobs.status');
        Route::post('/jobs/bulk',     [DispatchController::class,'bulkAssign'])->name('jobs.bulk');
    });

    // ปฏิทินงาน / borrow (พื้นที่เผื่ออนาคต)
    Route::view('/jobs/calendar', 'admin.jobs.calendar')->name('jobs.calendar');
    Route::view('/borrow/staff',   'admin.borrow.staff')->name('borrow.staff');
    Route::view('/borrow/vehicle', 'admin.borrow.vehicle')->name('borrow.vehicle');
    });

/*
|--------------------------------------------------------------------------
| Technician routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','can:manage-reports'])->prefix('admin')->name('admin.')->group(function () {
    Route::patch('/reports/{report}/assign', [AdminReportController::class, 'assign'])
        ->name('reports.assign');
});

Route::middleware(['auth','technician'])->prefix('technician')->name('tech.')->group(function () {
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{report}', [JobController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{report}/claim', [JobController::class, 'claim'])->name('jobs.claim');
    Route::post('/jobs/{report}/start', [JobController::class, 'start'])->name('jobs.start');
    Route::post('/jobs/{report}/complete', [JobController::class, 'complete'])->name('jobs.complete');
    // Route::view('/jobs/calendar', 'technician.jobs.calendar')->name('jobs.calendar');
});

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get   ('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch ('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',  [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Fallback 404 สวย ๆ (ลบได้ถ้าไม่ต้องการ)
Route::fallback(fn () => response()->view('errors.404', [], 404));

require __DIR__.'/auth.php';
