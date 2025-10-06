<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentLog;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JobController extends Controller
{
    public function index(Request $req)
    {
        $user = $req->user();
        $tab = $req->query('tab', 'my-queue');              // my-queue | unassigned | history
        $q = trim((string) $req->query('q', ''));
        $status = (string) $req->query('status', '');
        $unassigned = Report::query()
            ->leftJoin('assignments as a', function($j){
                $j->on('a.report_id','=','reports.id')
                ->whereIn('a.status', Assignment::OPEN_STATUSES);
            })
            ->whereNull('a.id') // ไม่มี assignment เปิดค้าง
            ->whereNotIn('reports.status', ['เสร็จสิ้น','ยกเลิก']) // 🚫 งานปิดแล้วไม่ต้องแสดง
            ->when(
                \Schema::hasColumn('reports','completed_at'),
                fn($q) => $q->whereNull('reports.completed_at') // ถ้ามีคอลัมน์นี้ก็กันไว้ด้วย
            )
            ->select('reports.*')
            ->latest('reports.created_at')
            ->paginate(10);

        $techs = User::query()
            ->where('role','technician')
            ->withCount(['assignments as open_jobs_count' => function($q){
                $q->whereIn('status', Assignment::OPEN_STATUSES);
            }])
            ->orderBy('open_jobs_count')
            ->get();

        $focus = (int) $req->query('focus', 0);

        // base scope สำหรับคำค้น + สถานะ
        $filter = function ($qr) use ($q, $status) {
            if ($q !== '') {
                $qr->where(function ($w) use ($q) {
                    $w->where('reports.device_address', 'like', "%{$q}%")
                      ->orWhere('reports.device_list', 'like', "%{$q}%")
                      ->orWhere('reports.detail', 'like', "%{$q}%");
                });
            }
            if ($status !== '') {
                $qr->where('reports.status', $status);
            }
        };

        if ($tab === 'unassigned') {
            // งานที่ยังไม่มี assignment เปิดอยู่
            $reports = Report::query()
                ->leftJoin('assignments as a', function ($j) {
                    $j->on('a.report_id', '=', 'reports.id')
                      ->whereIn('a.status', Assignment::OPEN_STATUSES);
                })
                ->whereNull('a.id')
                ->when(true, $filter)
                ->select('reports.*', DB::raw('NULL as priority'), DB::raw('NULL as due_at'))
                ->latest('reports.created_at')
                ->paginate(10)
                ->withQueryString();
        } elseif ($tab === 'history') {
            // งานของฉันที่ปิดแล้ว
            $reports = Report::query()
                ->join('assignments as a', 'a.report_id', '=', 'reports.id')
                ->where('a.technician_id', $user->id)
                ->whereIn('a.status', ['เสร็จสิ้น','ยกเลิก'])
                ->when(true, $filter)
                ->select('reports.*', 'a.priority', DB::raw('a.eta as due_at'), 'a.status')
                ->latest('a.updated_at')
                ->paginate(10)
                ->withQueryString();
        } else {
            // my-queue: งานของฉันที่ยังเปิดอยู่
            $reports = Report::query()
                ->join('assignments as a', 'a.report_id', '=', 'reports.id')
                ->where('a.technician_id', $user->id)
                ->whereIn('a.status', Assignment::OPEN_STATUSES)
                ->when(true, $filter)
                ->select('reports.*', 'a.priority', DB::raw('a.eta as due_at'), 'a.status')
                ->latest('a.created_at')
                ->paginate(10)
                ->withQueryString();
        }

        return view('technician.jobs.index', compact('tab','q','status','reports','unassigned','techs','focus'));
    }

    // รับงานตัวเองจากคิว unassigned
    public function claim(Request $req, Report $report)
    {
        $techId = $req->user()->id;

        return DB::transaction(function () use ($report, $techId) {
            // กันแข่ง: ล็อก report แถวนี้
            $rep = Report::lockForUpdate()->findOrFail($report->id);

            // มี assignment เปิดอยู่แล้วหรือยัง
            $exists = Assignment::open()->where('report_id', $rep->id)->exists();
            if ($exists) {
                return back()->withErrors('งานนี้ถูกมอบหมายไปแล้ว');
            }

            $a = Assignment::create([
                'report_id'     => $rep->id,
                'technician_id' => $techId,
                'assigned_by'   => $techId,           // self-claim
                'status'        => 'มอบหมาย',
                'priority'      => 3,
            ]);

            AssignmentLog::create([
                'assignment_id' => $a->id,
                'actor_id'      => $techId,
                'action'        => 'assign',
                'to_status'     => 'มอบหมาย',
                'note'          => 'technician self-claim',
                'ip'            => $req->ip(),
            ]);

            // อัปเดตสถานะ report ถ้าต้องการให้สัมพันธ์กัน
            if (Schema::hasColumn('reports', 'status')) {
                $report->update(['status' => 'กำลังดำเนินการ']);
            }

            return back()->with('success', 'รับงานเรียบร้อย');
        });
    }

    // กดเริ่มงาน
    public function start(Request $req, Report $report)
    {
        $techId = $req->user()->id;

        return DB::transaction(function () use ($report, $techId) {
            $a = Assignment::lockForUpdate()
                ->where('report_id', $report->id)
                ->where('technician_id', $techId)
                ->whereIn('status', ['มอบหมาย','กำลังดำเนินการ'])
                ->latest('id')->first();

            if (!$a) return back()->withErrors('ไม่พบงานของคุณในสถานะที่เริ่มได้');

            $from = $a->status;
            $a->update([
                'status'     => 'กำลังดำเนินการ',
                'started_at' => $a->started_at ?: now(),
            ]);

            AssignmentLog::create([
                'assignment_id' => $a->id,
                'actor_id'      => $techId,
                'action'        => 'status_change',
                'from_status'   => $from,
                'to_status'     => 'กำลังดำเนินการ',
                'ip'            => $req->ip(),
            ]);

            return back()->with('success', 'เริ่มงานแล้ว');
        });
    }

    // กดเสร็จงาน
    public function complete(Request $req, Report $report)
    {
        $techId = $req->user()->id;

        return DB::transaction(function () use ($report, $techId) {
            $a = Assignment::lockForUpdate()
                ->where('report_id', $report->id)
                ->where('technician_id', $techId)
                ->whereIn('status', ['มอบหมาย','กำลังดำเนินการ'])
                ->latest('id')->first();

            if (!$a) return back()->withErrors('ไม่พบงานของคุณในสถานะที่ปิดได้');

            $from = $a->status;
            $a->update([
                'status'      => 'เสร็จสิ้น',
                'finished_at' => now(),
            ]);

            AssignmentLog::create([
                'assignment_id' => $a->id,
                'actor_id'      => $techId,
                'action'        => 'status_change',
                'from_status'   => $from,
                'to_status'     => 'เสร็จสิ้น',
                'ip'            => $req->ip(),
            ]);

            // อัปเดตสถานะ report ถ้าต้องการให้สัมพันธ์กัน
            if (Schema::hasColumn('reports', 'status')) {
                $report->update(['status' => 'เสร็จสิ้น']);
            }

            return back()->with('success', 'ปิดงานเรียบร้อย');
        });
    }

    public function show(Request $req, Report $report)
    {
        $user = $req->user();

        $report->load([
            'user:id,name',           // ผู้แจ้ง
            'images',                 // แกลเลอรี
            'currentAssignment.technician:id,name',
            'latestAssignment.technician:id,name',
        ]);

        // ตรวจสิทธิ์แบบง่าย:
        // - ถ้าไม่มี assignment เปิดอยู่ -> ใครก็เห็นได้ (สำหรับช่างจะกดรับ)
        // - ถ้ามี assignment เปิดอยู่ -> ต้องเป็นของช่างคนนี้ถึงจะเห็น
        $current = $report->currentAssignment;
        if ($current && (int)$current->technician_id !== (int)$user->id) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าดูงานนี้');
        }

        return view('technician.jobs.show', compact('report'));
    }

    public function queue(Request $req)
    {
        $q = trim($req->input('q',''));
        $status = $req->input('status'); // จากดรอปดาวน์ "ทุกสถานะ/รอดำเนินการ/..."

        $reports = Report::query()
            ->when($q !== '', function($qq) use ($q){
                $qq->where(function($w) use ($q){
                    $w->where('device_address','like',"%$q%")
                    ->orWhere('device_list','like',"%$q%")
                    ->orWhere('detail','like',"%$q%");
                });
            })
            // ถ้าไม่เลือกสถานะ ให้แสดงเฉพาะงานที่ "ยังเปิดอยู่"
            ->when(($status ?? '') === '', fn($qq) => $qq->active())
            // ถ้าเลือกสถานะมา ก็กรองตามนั้น (แต่ยังกันไม่ให้เลือก 'เสร็จสิ้น/ยกเลิก' ติดมาได้)
            ->when(($status ?? '') !== '', fn($qq) => $qq->where('status', $status)->active())
            ->latest('id')
            ->paginate(10);

        return view('reports.queue', compact('reports','q','status'));
    }

}
