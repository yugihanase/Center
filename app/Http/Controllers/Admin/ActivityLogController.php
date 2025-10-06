<?php
// app/Http/Controllers/Admin/ActivityLogController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\ActivityLog;
use App\Models\AssignmentLog;
use App\Models\StockLog;              // ★ เพิ่ม

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('perPage', 50);

        // ---------- A) แอปล็อกเดิม ----------
        $appLogs = ActivityLog::query()
            ->with(['user:id,name'])
            ->latest('performed_at')
            ->limit(500)
            ->get()
            ->map(function ($a) {
                $props = $a->properties ?? null;
                if (is_string($props)) { $d = json_decode($props, true); if (json_last_error()===JSON_ERROR_NONE) $props = $d; }
                return (object) [
                    'ts'      => $a->performed_at ?? $a->created_at,
                    'user'    => $a->user?->name ?? '-',
                    'event'   => $a->event ?? $a->action ?? 'event',
                    'subject' => $this->renderSubject($a->subject_type ?? null, $a->subject_id ?? null),
                    'detail'  => $a->description ?? '',
                    'props'   => $props,
                    'ip'      => $a->ip_address ?? ($props['ip'] ?? '-'),
                    'source'  => 'app',
                ];
            });

        // ---------- B) ล็อกการมอบหมาย ----------
        $assignLogs = AssignmentLog::query()
            ->with([
                'actor:id,name',
                'assignment.report:id,device_address,device_list',
                'assignment.technician:id,name',
            ])
            ->latest('created_at')
            ->limit(500)
            ->get()
            ->map(function ($l) {
                $rep  = $l->assignment?->report;
                $tech = $l->assignment?->technician?->name;

                $subject = $rep
                    ? "Report #{$l->assignment->report_id} — {$rep->device_address}"
                    : "Report #{$l->assignment->report_id}";

                $detail = trim(collect([
                    $tech ? "ผู้รับผิดชอบ: {$tech}" : null,
                    ($l->from_status || $l->to_status) ? "สถานะ: {$l->from_status} → {$l->to_status}" : null,
                    $l->note ? "หมายเหตุ: {$l->note}" : null,
                ])->filter()->implode(' | '));

                return (object) [
                    'ts'      => $l->created_at,
                    'user'    => $l->actor?->name ?? '-',
                    'event'   => $this->actionLabel($l->action),
                    'subject' => $subject,
                    'detail'  => $detail,
                    'props'   => [
                        'assignment_id' => $l->assignment_id,
                        'technician'    => $tech,
                        'from_status'   => $l->from_status,
                        'to_status'     => $l->to_status,
                        'note'          => $l->note,
                    ],
                    'ip'      => $l->ip ?? '-',
                    'source'  => 'assignment',
                ];
            });

        // ---------- C) ★★ เพิ่ม: ล็อกสต็อก (รับเข้า/เบิกออก) ----------
        $stockLogs = StockLog::query()
            ->with([
                'stock:id,name,unit,stock_category_id',
                'stock.category:id,name',
                'user:id,name',
            ])
            ->latest('created_at')
            ->limit(500)
            ->get()
            ->map(function ($s) {
                $dirLabel = $s->direction === 'in' ? 'stock_in' : 'stock_out';
                $catName  = $s->stock?->category?->name;
                $unit     = $s->stock?->unit;

                $subject = $s->stock
                    ? "Stock: {$s->stock->name}".($catName ? " ({$catName})" : '')
                    : "Stock #{$s->stock_id}";

                $detail = trim(collect([
                    "ทิศทาง: ".($s->direction === 'in' ? 'รับเข้า' : 'เบิกออก'),
                    "จำนวน: {$s->qty}".($unit ? " {$unit}" : ''),
                    $s->note ? "หมายเหตุ: {$s->note}" : null,
                ])->implode(' | '));

                return (object) [
                    'ts'      => $s->created_at,
                    'user'    => $s->user?->name ?? '-',
                    'event'   => $dirLabel, // stock_in | stock_out
                    'subject' => $subject,
                    'detail'  => $detail,
                    'props'   => [
                        'stock_id'    => $s->stock_id,
                        'direction'   => $s->direction,
                        'qty'         => (int) $s->qty,
                        'unit'        => $unit,
                        'category'    => $catName,
                        'note'        => $s->note,
                    ],
                    'ip'      => '-',       // ถ้าจะเก็บ IP เพิ่มที่ stock_logs ค่อยเติมภายหลัง
                    'source'  => 'stock',
                ];
            });

        // ---------- D) รวม + เรียง + แบ่งหน้า ----------
        $all  = $appLogs->concat($assignLogs)->concat($stockLogs)->sortByDesc('ts')->values();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $logs = new LengthAwarePaginator(
            $all->slice(($page-1)*$perPage, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path'=>$request->url(),'query'=>$request->query()]
        );

        return view('admin.activity_logs.index', compact('logs','perPage'));
    }

    private function actionLabel(?string $key): string
    {
        return [
            'assign'         => 'assign',
            'assign_update'  => 'assign',
            'status_change'  => 'status',
            'claim'          => 'claim',
            'start'          => 'start',
            'complete'       => 'complete',
            'unassign'       => 'unassign',
        ][$key] ?? ($key ?: 'event');
    }

    private function renderSubject($type, $id): string
    {
        if (!$type || !$id) return '-';
        return class_basename($type)." #{$id}";
    }
}
