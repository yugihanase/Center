<?php

// app/Http/Controllers/UserReportController.php
namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserReportController extends Controller
{
    // หน้าเดียว: ฟอร์ม + ตารางของ "ฉัน"
    public function index(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $status = $request->get('status', '');

        $reports = Report::query()
            ->where('user_id', Auth::id())
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('device_address', 'like', "%{$q}%")
                      ->orWhere('device_list', 'like', "%{$q}%")
                      ->orWhere('detail', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', fn($qr) => $qr->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('report.follow', [
            'q'       => $q,
            'status'  => $status,
            'reports' => $reports,
            'isAdmin' => false, // ใช้คุมปุ่มใน Blade
        ]);
    }

    // ส่งคำขอแจ้งซ่อม

    public function create() {
        return view('report.create');
    }

    public function store(Request $request) {
        // 5.1 validate ข้อมูลฟอร์มหลัก + รูปหลายไฟล์
        $data = $request->validate([
            'device_address' => ['required','string','max:255'],
            'device_list'    => ['required','string','max:255'],
            'detail'         => ['required','string','max:2000'],

            'images'   => ['nullable','array','max:10'],
            'images.*' => ['nullable','image','mimes:jpeg,png,jpg,gif,webp','max:2048'], // KB (2MB)
        ]);

        // 5.2 สร้าง report
        $report = Report::create([
            'device_address' => $data['device_address'],
            'device_list'    => $data['device_list'],
            'detail'         => $data['detail'],
            'user_id'        => auth()->id() ?? null,
            'status'         => 'รอดำเนินการ',
        ]);

        // 5.3 บันทึกรูป (ถ้ามี)
        foreach ($request->file('images', []) as $file) {
            $path = $file->store('uploads/report_images', 'public'); // storage/app/public/...

            $report->images()->create([
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime'          => $file->getClientMimeType(),
                'size_kb'       => $file->getSize() ? intval($file->getSize()/1024) : null,
            ]);
        }

        return redirect()->route('report.show', $report)->with('success', 'ส่งคำขอเรียบร้อย');
    }

    public function show(Report $report) {
        $report->load('images');
        return view('report.show', compact('report'));
    }
}

