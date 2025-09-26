<?php

// app/Http/Controllers/ReportController.php
namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    // หน้าเดียว: ฟอร์ม + ตารางติดตาม
    public function follow(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status'); // อาจเป็น null หรือ ''

        $reports = \App\Models\Report::query()
            ->with('user')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('device_address', 'like', "%{$q}%")
                    ->orWhere('device_list', 'like', "%{$q}%")
                    ->orWhere('detail', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
                });
            })
            // ใส่ where เฉพาะเมื่อมีการเลือกสถานะจริง ๆ
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            // คงพารามิเตอร์บน pagination
            ->appends($request->query());

        return view('report.follow', [
            'reports' => $reports,
            'q'       => $q,
            'status'  => $status ?? '', // ส่งกลับไปให้ select ทำงานถูก
        ]);
    }

    // บันทึกฟอร์มแจ้งซ่อม
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_address' => ['required','string','max:255'],
            'device_list'    => ['required','string','max:255'],
            'detail'         => ['required','string','max:2000'],
        ], [
            'device_address.required' => 'กรุณากรอกที่อยู่อุปกรณ์',
            'device_list.required'    => 'กรุณากรอกรายการอุปกรณ์',
            'detail.required'         => 'กรุณาแจ้งรายละเอียด',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status']  = 'รอดำเนินการ';

        Report::create($validated);

        return back()->with('success', 'ส่งคำขอเรียบร้อย');
    }

    // เปลี่ยนสถานะแบบเร็ว
    public function updateStatus(Request $request, Report $report)
    {
        $request->validate([
            'status' => ['required','in:รอดำเนินการ,กำลังดำเนินการ,เสร็จสิ้น,ยกเลิก']
        ]);

        $report->update(['status' => $request->status]);

        return back()->with('success', 'อัปเดตสถานะสำเร็จ');
    }

    // ลบรายการ
    public function destroy(Report $report)
    {
        $report->delete();
        return back()->with('success', 'ลบรายการแล้ว');
    }
}
