<?php

// app/Http/Controllers/Admin/DashboardController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // สรุปงานซ่อม
        $job_total   = Report::count();
        $job_wait    = Report::where('status', 'รอดำเนินการ')->count();
        $job_doing   = Report::where('status', 'กำลังดำเนินการ')->count();
        $job_done    = Report::where('status', 'เสร็จสิ้น')->count();

        // สรุปคน (โฟกัส role=technician เป็น “ช่าง”)
        $tech_total  = User::where('role', 'technician')->count();
        $tech_busy   = User::where('role', 'technician')->where('is_busy', true)->count();   // ต้องมีคอลัมน์ is_busy
        $tech_idle   = $tech_total - $tech_busy;

        // สรุปยานพาหนะ
        $veh_total   = Vehicle::count();                                   // ต้องมีตาราง vehicles
        $veh_inuse   = Vehicle::where('status', 'in_use')->count();
        $veh_free    = Vehicle::where('status', 'available')->count();

        // รายการงานล่าสุด (โชว์ 10 แถว)
        $latestReports = Report::with('user')->latest()->limit(10)->get();

        return view('admin.dashboard', compact(
            'job_total','job_wait','job_doing','job_done',
            'tech_total','tech_busy','tech_idle',
            'veh_total','veh_inuse','veh_free',
            'latestReports'
        ));
    }
}
