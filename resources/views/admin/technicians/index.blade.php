{{-- resources/views/admin/technicians/index.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">จัดการรายชื่อช่าง</h2>
  </x-slot>

  <div class="container py-3">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <form method="GET" class="row g-2">
          <div class="col-md-4">
            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="ค้นหา รหัส/ชื่อ/เบอร์/อีเมล/ฝ่าย">
          </div>
          <div class="col-md-3">
            <select name="role" class="form-select">
              <option value="">ทุกบทบาท</option>
              <option value="technician" @selected($role==='technician')>ช่าง</option>
              <option value="lead" @selected($role==='lead')>หัวหน้าช่าง</option>
            </select>
          </div>
          <div class="col-md-3">
            <select name="active" class="form-select">
              <option value="">ทั้งหมด (เปิด/ปิด)</option>
              <option value="1" @selected($active==='1')>เฉพาะเปิดใช้งาน</option>
              <option value="0" @selected($active==='0')>เฉพาะปิดใช้งาน</option>
            </select>
          </div>
          <div class="col-md-2 d-grid">
            <button class="btn btn-primary">ค้นหา</button>
          </div>
        </form>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">รายชื่อช่าง ({{ $techs->total() }})</h5>
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa fa-plus-circle me-1"></i> เพิ่มจากรหัสพนักงาน
      </button>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>รหัสพนักงาน</th>
              <th>ชื่อ</th>
              <th>บทบาท</th>
              <th>เบอร์</th>
              <th>อีเมล</th>
              <th>ฝ่าย</th>
              <th>สถานะ</th>
              <th class="text-end">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($techs as $t)
              <tr>
                <td>{{ $t->employee_code }}</td>
                <td>{{ $t->name }}</td>
                <td>
                  @if ($t->role === 'lead')
                    <span class="badge bg-dark">หัวหน้าช่าง</span>
                  @else
                    <span class="badge bg-secondary">ช่าง</span>
                  @endif
                </td>
                <td>{{ $t->phone }}</td>
                <td>{{ $t->email }}</td>
                <td>{{ $t->department }}</td>
                <td>
                  <span class="badge {{ $t->is_active ? 'bg-success' : 'bg-danger' }}">
                    {{ $t->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                  </span>
                </td>
                <td class="text-end">
                  {{-- ปุ่มเลื่อนตำแหน่ง/ลดตำแหน่ง --}}
                  @if ($t->role === 'lead')
                    <form method="POST" action="{{ route('admin.technicians.demote', $t) }}" class="d-inline">
                      @csrf @method('PATCH')
                      <button class="btn btn-outline-warning btn-sm">ลดเป็นช่าง</button>
                    </form>
                  @else
                    <form method="POST" action="{{ route('admin.technicians.promote', $t) }}" class="d-inline">
                      @csrf @method('PATCH')
                      <button class="btn btn-outline-dark btn-sm">ตั้งเป็นหัวหน้า</button>
                    </form>
                  @endif

                  {{-- เปิด/ปิดการใช้งาน --}}
                  <form method="POST" action="{{ route('admin.technicians.toggle', $t) }}" class="d-inline">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-secondary btn-sm">
                      {{ $t->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}
                    </button>
                  </form>

                  {{-- แก้ไข (ใช้ modal ร่วมกัน) --}}
                  <button class="btn btn-primary btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#editModal{{ $t->id }}">แก้ไข</button>

                  {{-- ลบ --}}
                  <form method="POST" action="{{ route('admin.technicians.destroy', $t) }}" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">ลบ</button>
                  </form>
                </td>
              </tr>

              {{-- Edit Modal --}}
              <div class="modal fade" id="editModal{{ $t->id }}" tabindex="-1">
                <div class="modal-dialog">
                  <form class="modal-content" method="POST" action="{{ route('admin.technicians.update', $t) }}">
                    @csrf @method('PATCH')
                    <div class="modal-header">
                      <h5 class="modal-title">แก้ไขช่าง: {{ $t->name }}</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      @include('admin.technicians.partials.form', ['item' => $t])
                    </div>
                    <div class="modal-footer">
                      <button class="btn btn-primary">บันทึก</button>
                    </div>
                  </form>
                </div>
              </div>
            @empty
              <tr><td colspan="8" class="text-center text-muted">— ไม่มีข้อมูล —</td></tr>
            @endforelse
          </tbody>
        </table>

        {{ $techs->onEachSide(1)->links() }}
      </div>
    </div>
  </div>

  {{-- Add Modal --}}
  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="POST" action="{{ route('admin.technicians.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">เพิ่มรายชื่อช่างจากรหัสพนักงาน</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          @include('admin.technicians.partials.form', ['item' => null])
        </div>
        <div class="modal-footer">
          <button class="btn btn-success">เพิ่ม</button>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
