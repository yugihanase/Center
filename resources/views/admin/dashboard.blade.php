<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('ระบบจัดการงานช่าง') }}
    </h2>
  </x-slot>

  <div class="container-fluid py-3">
    {{-- แถวสรุปงานซ่อม --}}
    <div class="row g-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="fw-semibold">จำนวนงานซ่อมทั้งหมด</div>
                <div class="display-6">{{ $job_total }}</div>
              </div>
              <i class="fas fa-tools fa-2x text-secondary"></i>
            </div>
            <div class="mt-2 small text-muted-white">
              รอดำเนินการ: {{ $job_wait }} | กำลังดำเนินการ: {{ $job_doing }} | เสร็จสิ้น: {{ $job_done }}
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-secondary text-white">
          <div class="card-body">
            <div class="fw-semibold mb-1">รอดำเนินการ</div>
            <div class="display-6">{{ $job_wait }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-white">
          <div class="card-body">
            <div class="fw-semibold mb-1">กำลังดำเนินการ</div>
            <div class="display-6">{{ $job_doing }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
          <div class="card-body">
            <div class="fw-semibold mb-1">ดำเนินการเสร็จแล้ว</div>
            <div class="display-6">{{ $job_done }}</div>
          </div>
        </div>
      </div>
    </div>

    {{-- แถวสรุป คน/รถ --}}
    <div class="row g-3 mt-1">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-semibold">จำนวนคน (ช่าง)</div>
              <i class="fas fa-user-cog"></i>
            </div>
            <div class="row text-center">
              <div class="col">
                <div class="small text-muted">รวม</div>
                <div class="h3">{{ $tech_total }}</div>
              </div>
              <div class="col">
                <div class="small text-muted">กำลังทำงาน</div>
                <div class="h3">{{ $tech_busy }}</div>
              </div>
              <div class="col">
                <div class="small text-muted">ว่าง</div>
                <div class="h3">{{ $tech_idle }}</div>
              </div>
            </div>
            <canvas id="techChart" height="120" class="mt-3"></canvas>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-semibold">จำนวนรถ</div>
              <i class="fas fa-truck"></i>
            </div>
            <div class="row text-center">
              <div class="col">
                <div class="small text-muted">รวม</div>
                <div class="h3">{{ $veh_total }}</div>
              </div>
              <div class="col">
                <div class="small text-muted">กำลังใช้งาน</div>
                <div class="h3">{{ $veh_inuse }}</div>
              </div>
              <div class="col">
                <div class="small text-muted">ว่าง</div>
                <div class="h3">{{ $veh_free }}</div>
              </div>
            </div>
            <canvas id="vehChart" height="120" class="mt-3"></canvas>
          </div>
        </div>
      </div>
    </div>

    {{-- รายการงานล่าสุด + เมนูลัดไปหน้า “เตรียมพื้นที่ไว้ก่อน” --}}
    <div class="row g-3 mt-1">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-header fw-semibold">ตารางสรุปงานล่าสุด</div>
          <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>ที่อยู่อุปกรณ์</th>
                  <th>รายการ</th>
                  <th>สถานะ</th>
                  <th>ผู้แจ้ง</th>
                  <th>เมื่อ</th>
                  <th>จัดการ</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($latestReports as $i => $r)
                  <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $r->device_address }}</td>
                    <td>{{ $r->device_list }}</td>
                    <td>
                      @php
                        $map=['รอดำเนินการ'=>'secondary','กำลังดำเนินการ'=>'warning','เสร็จสิ้น'=>'success','ยกเลิก'=>'dark'];
                      @endphp
                      <span class="badge bg-{{ $map[$r->status] ?? 'secondary' }}">{{ $r->status }}</span>
                    </td>
                    <td>{{ $r->user?->name ?? '-' }}</td>
                    <td>{{ $r->created_at?->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.reports.show', $r) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-zoom-in"></i>
                        </a>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="text-center text-muted">ยังไม่มีข้อมูล</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header fw-semibold">ระบบ/เมนูด่วน</div>
          <div class="card-body d-grid gap-2">
            <a class="btn btn-outline-primary" href="{{ route('admin.jobs.assign') }}">
              ระบบจ่ายงานช่าง (เตรียมพื้นที่)
            </a>
            <a class="btn btn-outline-secondary" href="{{ route('admin.borrow.staff') }}">
              แจ้งยืมคน (เตรียมพื้นที่)
            </a>
            <a class="btn btn-outline-dark" href="{{ route('admin.borrow.vehicle') }}">
              แจ้งยืมรถ (เตรียมพื้นที่)
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Chart.js --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // คน (ช่าง)
    new Chart(document.getElementById('techChart'), {
      type: 'doughnut',
      data: {
        labels: ['กำลังทำงาน','ว่าง'],
        datasets: [{ data: [{{ $tech_busy }}, {{ $tech_idle }}] }]
      }
    });

    // รถ
    new Chart(document.getElementById('vehChart'), {
      type: 'doughnut',
      data: {
        labels: ['กำลังใช้งาน','ว่าง'],
        datasets: [{ data: [{{ $veh_inuse }}, {{ $veh_free }}] }]
      }
    });
  </script>
</x-app-layout>
