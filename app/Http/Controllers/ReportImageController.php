<?php
// app/Http/Controllers/ReportImageController.php
namespace App\Http\Controllers;

use App\Models\ReportImage;
use Illuminate\Support\Facades\Storage;

class ReportImageController extends Controller
{
    // ลบรูปเดี่ยว
    public function destroy(ReportImage $image)
    {
        // ลบไฟล์จริงใน storage ถ้ามี
        if ($image->path && Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        // ลบเรคคอร์ด
        $image->delete();

        return back()->with('success', 'ลบรูปเรียบร้อย');
    }
}
