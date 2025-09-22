<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Models\Report; // ถ้าจะบันทึกลง DB
use App\Models\User;

class ReportController extends Controller
{
    public function create()
    {
        return view('report.create');
    }

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

        // ถ้าบันทึกฐานข้อมูล:
        // Report::create($validated);

        return back()->with('success', 'ส่งคำขอเรียบร้อย');
    }

    public function follow(Request $request)
    {
        // ตัวอย่าง filter เบื้องต้น (ค้นหาจากชื่อ/อีเมล)
        $search = $request->query('q');

        $users = User::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('follow', compact('users', 'search'));
    }
    
    
}
