<?php
// app/Http/Controllers/Admin/TechnicianController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TechnicianRequest;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;

class TechnicianController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->input('q',''));
        $role = $request->input('role');
        $active = $request->input('active');

        $techs = Technician::query()
            ->when($role, fn($w) => $w->where('role',$role))
            ->when($active !== null && $active !== '', fn($w) => $w->where('is_active', (bool)$active))
            ->search($q)
            ->orderBy('role')       // lead มาก่อน
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $users = User::query()->select('id','name','email','employee_code')->orderBy('name')->get();

        return view('admin.technicians.index', compact('techs','q','role','active','users'));
    }

    public function store(TechnicianRequest $request)
    {
        $data = $request->validated();

        // ถ้าไม่ได้กรอก user_id แต่มี users.employee_code ตรงกัน ให้ auto-link
        if (empty($data['user_id']) && isset($data['employee_code'])) {
            $u = User::where('employee_code', $data['employee_code'])->first();
            if ($u) $data['user_id'] = $u->id;
        }

        Technician::create($data);

        return back()->with('success','เพิ่มช่างเรียบร้อย');
    }

    public function update(TechnicianRequest $request, Technician $technician)
    {
        $data = $request->validated();

        if (empty($data['user_id']) && isset($data['employee_code'])) {
            $u = \App\Models\User::where('employee_code', $data['employee_code'])->first();
            if ($u) $data['user_id'] = $u->id;
        }

        $technician->update($data);

        return back()->with('success','บันทึกข้อมูลช่างแล้ว');
    }

    public function destroy(Technician $technician)
    {
        // ลบได้เฉพาะกรณีไม่ผูกกับงานสำคัญ (คุณค่อยเช็ค FK ภายหลังถ้าจะใช้ technicians.id ไปอ้างอิง)
        $technician->delete();
        return back()->with('success','ลบช่างแล้ว');
    }

    public function toggle(Technician $technician)
    {
        $technician->is_active = !$technician->is_active;
        $technician->save();

        return back()->with('success', $technician->is_active ? 'เปิดใช้งานช่างแล้ว' : 'ปิดใช้งานช่างแล้ว');
    }

    public function promote(Technician $technician)
    {
        $technician->role = 'lead';
        $technician->save();
        return back()->with('success','ตั้งเป็นหัวหน้าช่างแล้ว');
    }

    public function demote(Technician $technician)
    {
        $technician->role = 'technician';
        $technician->save();
        return back()->with('success','ปรับเป็นช่างทั่วไปแล้ว');
    }
}
