<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        // validate พารามิเตอร์กรอง/เรียง/หน้า
        $data = $request->validate([
            'q'       => ['nullable', 'string', 'max:255'],
            'status'  => ['nullable', Rule::in(['รอดำเนินการ','กำลังดำเนินการ','เสร็จสิ้น','ยกเลิก'])],
            'from'    => ['nullable','date'],
            'to'      => ['nullable','date','after_or_equal:from'],
            'sort'    => ['nullable', Rule::in(['created_at','status','device_address','device_list','id'])],
            'dir'     => ['nullable', Rule::in(['asc','desc'])],
            'perPage' => ['nullable','integer','min:5','max:100'],
        ]);

        $sort = $data['sort']    ?? 'created_at';
        $dir  = $data['dir']     ?? 'desc';
        $per  = $data['perPage'] ?? 15;

        $reports = Report::query()
            ->with('user')
            ->search($data['q'] ?? null)
            ->status($data['status'] ?? null)
            ->dateRange($data['from'] ?? null, $data['to'] ?? null)
            ->orderBy($sort, $dir)
            ->paginate($per)
            ->withQueryString();

        return view('admin.reports.index', [
            'q'        => $data['q']      ?? '',
            'status'   => $data['status'] ?? '',
            'from'     => $data['from']   ?? '',
            'to'       => $data['to']     ?? '',
            'sort'     => $sort,
            'dir'      => $dir,
            'perPage'  => $per,
            'reports'  => $reports,
            'isAdmin'  => true,
        ]);
    }

    public function show(Report $report)
    {
        $report->load(['user']); // เผื่อหน้า view ต้องใช้ชื่อผู้แจ้ง
        return view('admin.reports.show', compact('report'));
    }

    public function updateStatus(Request $request, Report $report)
    {
        $request->validate([
            'status' => ['required', Rule::in(['รอดำเนินการ','กำลังดำเนินการ','เสร็จสิ้น','ยกเลิก'])]
        ]);

        $report->update(['status' => $request->status]);

        return back()->with('success', 'อัปเดตสถานะสำเร็จ');
    }

    /** อัปเดตสถานะแบบเลือกหลายแถว */
    public function bulkUpdateStatus(Request $request)
    {
        $data = $request->validate([
            'ids'    => ['required','array','min:1'],
            'ids.*'  => ['integer','exists:reports,id'],
            'status' => ['required', Rule::in(['รอดำเนินการ','กำลังดำเนินการ','เสร็จสิ้น','ยกเลิก'])],
        ]);

        Report::whereIn('id', $data['ids'])->update(['status' => $data['status']]);

        return back()->with('success', 'อัปเดตสถานะหลายรายการสำเร็จ');
    }

    public function destroy(Report $report)
    {
        $report->delete();
        return back()->with('success', 'ลบรายการแล้ว');
    }

    /** ลบหลายแถว */
    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required','array','min:1'],
            'ids.*' => ['integer','exists:reports,id'],
        ]);

        Report::whereIn('id', $data['ids'])->delete();

        return back()->with('success', 'ลบหลายรายการแล้ว');
    }

    /** ส่งออก CSV ตามเงื่อนไขกรองในหน้า */
    public function export(Request $request): StreamedResponse
    {
        // ใช้กฎเดียวกับ index() แต่ไม่ต้อง perPage
        $data = $request->validate([
            'q'      => ['nullable','string','max:255'],
            'status' => ['nullable', Rule::in(['รอดำเนินการ','กำลังดำเนินการ','เสร็จสิ้น','ยกเลิก'])],
            'from'   => ['nullable','date'],
            'to'     => ['nullable','date','after_or_equal:from'],
            'sort'   => ['nullable', Rule::in(['created_at','status','device_address','device_list','id'])],
            'dir'    => ['nullable', Rule::in(['asc','desc'])],
        ]);

        $sort = $data['sort'] ?? 'created_at';
        $dir  = $data['dir']  ?? 'desc';

        $rows = Report::query()
            ->with('user')
            ->search($data['q'] ?? null)
            ->status($data['status'] ?? null)
            ->dateRange($data['from'] ?? null, $data['to'] ?? null)
            ->orderBy($sort, $dir)
            ->get(['id','device_address','device_list','detail','status','user_id','created_at']);

        $filename = 'reports_export_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Device Address','Device List','Detail','Status','User','Created At']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->device_address,
                    $r->device_list,
                    $r->detail,
                    $r->status,
                    optional($r->user)->name ?: '-',
                    optional($r->created_at)?->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
