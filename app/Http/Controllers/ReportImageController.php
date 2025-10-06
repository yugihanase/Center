<?php
// app/Http/Controllers/ReportImageController.php
namespace App\Http\Controllers;

use App\Models\ReportImage;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

class ReportImageController extends Controller
{
    public function store(Request $request, Report $report)
    {
        // อนุญาตเฉพาะเจ้าของงานหรือแอดมิน และห้ามอัปโหลดถ้างานปิดแล้ว
        $isOwner = $request->user()->id === $report->user_id;
        $isAdmin = $request->user()->role === 'admin';
        $locked  = in_array($report->status, ['เสร็จสิ้น','ยกเลิก'], true);

        abort_unless(($isOwner || $isAdmin) && !$locked, 403);

        // ตรวจสอบไฟล์: สูงสุด 10 ไฟล์, รูปภาพ, ≤ 2MB/ไฟล์
        $validated = $request->validate([
            'images'   => ['required','array','max:10'],
            'images.*' => ['file','image','mimes:jpeg,jpg,png,webp','max:2048'],
        ], [], [
            'images'   => 'ไฟล์รูป',
            'images.*' => 'ไฟล์รูป',
        ]);

        foreach ($validated['images'] as $file) {
            $path = $file->store('reports/'.$report->id, 'public');

            ReportImage::create([
                'report_id'     => $report->id,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime'          => $file->getClientMimeType(),
                'size'          => $file->getSize(),
            ]);
        }

        return back()->with('success_detail', 'อัปโหลดรูปเรียบร้อย');
    }

    public function destroy(Request $request, ReportImage $image)
    {
        $report  = $image->report;
        $isOwner = $request->user()->id === $report->user_id;
        $isAdmin = $request->user()->role === 'admin';
        $locked  = in_array($report->status, ['เสร็จสิ้น','ยกเลิก'], true);

        // ลบได้เฉพาะเจ้าของหรือแอดมิน และต้องไม่ล็อก
        abort_unless(($isOwner || $isAdmin) && !$locked, 403);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('success_detail', 'ลบรูปเรียบร้อย');
    }
}
