<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentLog;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobAssignController extends Controller
{
    public function index(Request $req)
    {
        // คิวที่ยังไม่ถูกมอบหมาย (กันงานปิด)
        $unassigned = Report::query()
            ->leftJoin('assignments as a', function($j){
                $j->on('a.report_id','=','reports.id')
                ->whereIn('a.status', Assignment::OPEN_STATUSES);
            })
            ->whereNull('a.id')
            ->whereNotIn('reports.status', ['เสร็จสิ้น','ยกเลิก'])
            ->select('reports.*')
            ->latest('reports.created_at')
            ->paginate(10); // ชื่อ page = page (ดีแล้ว)

        // รายชื่อช่าง + จำนวนงานเปิด
        $techs = User::query()
            ->where('role','technician')
            ->withCount(['assignments as open_jobs_count' => function($q){
                $q->whereIn('status', Assignment::OPEN_STATUSES);
            }])
            ->orderBy('name')
            ->get();

        // รับพารามิเตอร์ช่างที่ต้องการดู
        $viewTechId = (int) $req->query('view_tech', 0);

        // โหลดจาก DB ตรง ๆ (กันกรณีชนิดข้อมูล/firstWhere ไม่เจอ)
        $selectedTech = $viewTechId > 0
            ? User::where('role', 'technician')->find($viewTechId)
            : null;

        // ถ้าเลือกช่าง → ดึงงานเปิดของช่างคนนั้น
        $techOpen = null;
        if ($selectedTech) {
            $techOpen = Assignment::query()
                ->open() // scope: whereIn status มอบหมาย/กำลังดำเนินการ
                ->where('technician_id', $selectedTech->id)
                ->with([
                    'report:id,device_address,device_list,requester_name,created_at,status',
                    'technician:id,name,email',
                ])
                ->latest('created_at')
                ->paginate(10, ['*'], 'tech_page'); // ใช้ชื่อหน้า tech_page กันชนกับ page
        }

        return view('admin.jobs.assign', compact(
            'unassigned', 'techs', 'viewTechId', 'selectedTech', 'techOpen'
        ));
    }

    public function assign(Request $req)
    {
        $data = $req->validate([
            'report_id'     => ['required','exists:reports,id'],
            'technician_id' => ['required','exists:users,id'],
            'priority'      => ['nullable','integer','between:1,5'],
            'eta'           => ['nullable','date'],
            'note'          => ['nullable','string','max:2000'],
        ]);

        $userId = $req->user()->id;

        return DB::transaction(function() use ($data,$userId) {
            $report = Report::where('id',$data['report_id'])->lockForUpdate()->first();

            $exists = Assignment::open()->where('report_id',$report->id)->exists();
            if ($exists) abort(422, 'งานนี้ถูกมอบหมายอยู่แล้ว');

            $a = Assignment::create([
                'report_id'     => $report->id,
                'technician_id' => $data['technician_id'],
                'assigned_by'   => $userId,
                'status'        => 'มอบหมาย',
                'priority'      => $data['priority'] ?? 3,
                'eta'           => $data['eta'] ?? null,
                'note'          => $data['note'] ?? null,
            ]);

            AssignmentLog::create([
                'assignment_id' => $a->id,
                'actor_id'      => $userId,
                'action'        => 'assign',
                'to_status'     => 'มอบหมาย',
                'note'          => $data['note'] ?? null,
            ]);

            return response()->json(['ok'=>true,'assignment_id'=>$a->id]);
        });
    }

    public function reassign(Request $req)
    {
        $data = $req->validate([
            'assignment_id' => ['required','exists:assignments,id'],
            'technician_id' => ['required','exists:users,id'],
            'note'          => ['nullable','string','max:2000'],
        ]);

        $actor = $req->user()->id;

        return DB::transaction(function() use ($data,$actor){
            $a = Assignment::lockForUpdate()->find($data['assignment_id']);
            $oldTech = $a->technician_id;
            $a->update(['technician_id' => $data['technician_id']]);

            AssignmentLog::create([
                'assignment_id'=>$a->id,
                'actor_id'=>$actor,
                'action'=>'reassign',
                'note'=>"from:$oldTech to:{$data['technician_id']}".($data['note']? " | {$data['note']}" : ''),
            ]);

            return response()->json(['ok'=>true]);
        });
    }

    public function changeStatus(Request $req)
    {
        $data = $req->validate([
            'assignment_id' => ['required','exists:assignments,id'],
            'status'        => ['required','in:มอบหมาย,กำลังดำเนินการ,เสร็จสิ้น,ยกเลิก'],
            'note'          => ['nullable','string','max:2000'],
        ]);

        $actor = $req->user()->id;

        return DB::transaction(function() use ($data,$actor){
            $a = Assignment::lockForUpdate()->find($data['assignment_id']);
            $from = $a->status;
            $a->update(['status'=>$data['status']]);

            if ($data['status']==='กำลังดำเนินการ' && !$a->started_at) $a->update(['started_at'=>now()]);
            if ($data['status']==='เสร็จสิ้น' && !$a->finished_at)   $a->update(['finished_at'=>now()]);

            AssignmentLog::create([
                'assignment_id'=>$a->id,
                'actor_id'=>$actor,
                'action'=>'status_change',
                'from_status'=>$from,
                'to_status'=>$data['status'],
                'note'=>$data['note'] ?? null,
            ]);

            return response()->json(['ok'=>true]);
        });
    }

    public function bulkAssign(Request $req)
    {
        $data = $req->validate([
            'report_ids'    => ['required','array','min:1'],
            'report_ids.*'  => ['integer','exists:reports,id'],
            'technician_id' => ['required','exists:users,id'],
            'priority'      => ['nullable','integer','between:1,5'],
        ]);

        $userId = $req->user()->id;

        DB::transaction(function() use ($data,$userId) {
            foreach ($data['report_ids'] as $rid) {
                $open = Assignment::open()->where('report_id',$rid)->lockForUpdate()->exists();
                if ($open) continue;

                $a = Assignment::create([
                    'report_id'     => $rid,
                    'technician_id' => $data['technician_id'],
                    'assigned_by'   => $userId,
                    'status'        => 'มอบหมาย',
                    'priority'      => $data['priority'] ?? 3,
                ]);
                AssignmentLog::create([
                    'assignment_id'=>$a->id,
                    'actor_id'=>$userId,
                    'action'=>'assign',
                    'to_status'=>'มอบหมาย',
                ]);
            }
        });

        return response()->json(['ok'=>true]);
    }
}
