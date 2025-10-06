<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Assignment;              // เพิ่ม
use App\Models\User;                    // เพิ่ม
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;      // เพิ่ม

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
        // โหลด assignment ปัจจุบัน/ล่าสุด + ช่าง เพื่อแสดง "ผู้รับผิดชอบ" จริง
        $report->load([
            'user',
            'images',
            'currentAssignment.technician',
            'latestAssignment.technician',
        ]);

        // รายชื่อช่างสำหรับดรอปดาวน์มอบหมาย/ย้าย
        $techs = User::where('role','technician')->orderBy('name')->get(['id','name']);

        return view('admin.reports.show', compact('report','techs'));
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

    public function update(Request $request, Report $report)
    {
        $old = $report->only(['status','detail']);
        $report->update($request->validate([
            'status' => 'required|string',
            'detail' => 'required|string|max:2000',
        ]));
        $new = $report->only(['status','detail']);

        activity_log([
            'event'       => 'updated',
            'subject'     => $report,
            'description' => "แก้ไขงานแจ้งซ่อม #{$report->id}",
            'properties'  => ['before'=>$old,'after'=>$new],
        ]);

        return back()->with('success','อัปเดตเรียบร้อย');
    }

    /**
     * มอบหมาย/ย้ายผู้รับผิดชอบ (แอดมิน)
     */
    public function assign(Request $request, Report $report)
    {
        $data = $request->validate([
            'technician_id' => ['nullable','exists:users,id'],
            'priority'      => ['nullable','integer','between:1,5'],
            'eta'           => ['nullable','date'],
            'note'          => ['nullable','string','max:2000'],
        ]);

        return DB::transaction(function () use ($report, $data, $request) {
            // หา assignment ที่ยัง "เปิดอยู่" ของรายงานนี้
            $open = Assignment::lockForUpdate()
                ->where('report_id', $report->id)
                ->open()
                ->latest('id')
                ->first();

            // A) technician_id = null -> ปลดมอบหมาย
            if (empty($data['technician_id'])) {
                if ($open) {
                    $from = $open->status;
                    $open->update(['status' => 'ยกเลิก', 'finished_at' => now()]);
                    $open->logs()->create([
                        'actor_id'    => $request->user()->id,
                        'action'      => 'status_change',
                        'from_status' => $from,
                        'to_status'   => 'ยกเลิก',
                        'note'        => 'unassign by admin',
                    ]);
                }
                return back()->with('success','ยกเลิกการมอบหมายแล้ว');
            }

            // B) มี assignment เปิดอยู่ → เปลี่ยนช่าง/ปรับรายละเอียด
            if ($open) {
                $changes = [];
                if ($open->technician_id !== (int)$data['technician_id']) {
                    $open->technician_id = (int)$data['technician_id'];
                    $changes[] = 'เปลี่ยนผู้รับผิดชอบ';
                }
                if (isset($data['priority'])) { $open->priority = $data['priority']; $changes[] = 'ปรับ Priority'; }
                if (isset($data['eta']))      { $open->eta      = $data['eta'];      $changes[] = 'กำหนด ETA'; }
                if (isset($data['note']))     { $open->note     = $data['note']; }

                $open->save();
                $open->logs()->create([
                    'actor_id'  => $request->user()->id,
                    'action'    => 'assign_update',
                    'to_status' => $open->status,
                    'note'      => implode(' / ', $changes) ?: 'ปรับรายละเอียดมอบหมาย',
                    'ip'        => $request->ip(),
                ]);

                if ($report->status === 'รอดำเนินการ') {
                    $report->update(['status' => 'กำลังดำเนินการ']);
                }

                return back()->with('success','อัปเดตการมอบหมายเรียบร้อย');
            }

            // C) ยังไม่มี assignment เปิดอยู่ → สร้างใหม่
            $a = Assignment::create([
                'report_id'     => $report->id,
                'technician_id' => (int)$data['technician_id'],
                'assigned_by'   => $request->user()->id,
                'status'        => 'มอบหมาย',
                'priority'      => $data['priority'] ?? 3,
                'eta'           => $data['eta'] ?? null,
                'note'          => $data['note'] ?? null,
            ]);

            $a->logs()->create([
                'actor_id'  => $request->user()->id,
                'action'    => 'assign',
                'to_status' => 'มอบหมาย',
                'note'      => 'assigned by admin',
                'ip'        => $request->ip(),
            ]);

            if ($report->status === 'รอดำเนินการ') {
                $report->update(['status' => 'กำลังดำเนินการ']);
            }

            return back()->with('success','มอบหมายงานเรียบร้อย');
        });
    }
}
