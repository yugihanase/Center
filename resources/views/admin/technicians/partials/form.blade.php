{{-- resources/views/admin/technicians/partials/form.blade.php --}}
@php
  $item = $item ?? null;
@endphp

<div class="mb-3">
  <label class="form-label">รหัสพนักงาน *</label>
  <input type="text" name="employee_code" value="{{ old('employee_code', $item->employee_code ?? '') }}"
         class="form-control" required>
</div>

<div class="mb-3">
  <label class="form-label">ชื่อ-สกุล *</label>
  <input type="text" name="name" value="{{ old('name', $item->name ?? '') }}" class="form-control" required>
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">บทบาท *</label>
    <select name="role" class="form-select" required>
      @foreach (['technician'=>'ช่าง','lead'=>'หัวหน้าช่าง'] as $k=>$v)
        <option value="{{ $k }}" @selected(old('role', $item->role ?? 'technician')===$k)>{{ $v }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label">สังกัด/ฝ่าย</label>
    <input type="text" name="department" value="{{ old('department', $item->department ?? '') }}" class="form-control">
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">เบอร์โทร</label>
    <input type="text" name="phone" value="{{ old('phone', $item->phone ?? '') }}" class="form-control">
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label">อีเมล</label>
    <input type="email" name="email" value="{{ old('email', $item->email ?? '') }}" class="form-control">
  </div>
</div>

<div class="mb-3">
  <label class="form-label">ผูกกับผู้ใช้ (ถ้ามี)</label>
  <select name="user_id" class="form-select">
    <option value="">— ไม่ผูก —</option>
    @foreach ($users as $u)
      <option value="{{ $u->id }}"
        @selected( (string)old('user_id', $item->user_id ?? '') === (string)$u->id )>
        {{ $u->name }} {{ $u->email ? "({$u->email})" : '' }} {{ $u->employee_code ? "- {$u->employee_code}" : '' }}
      </option>
    @endforeach
  </select>
  <div class="form-text">ถ้าปล่อยว่าง ระบบจะพยายามจับคู่ด้วย employee_code อัตโนมัติเมื่อบันทึก</div>
</div>

<div class="mb-3">
  <label class="form-label">หมายเหตุ</label>
  <textarea name="notes" class="form-control" rows="2">{{ old('notes', $item->notes ?? '') }}</textarea>
</div>

@if($item)
  <div class="form-check">
    <input class="form-check-input" type="checkbox" value="1" name="is_active" id="is_active"
           @checked(old('is_active', $item->is_active))>
    <label class="form-check-label" for="is_active">เปิดใช้งาน</label>
  </div>
@endif
