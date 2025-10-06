{{-- resources/views/technician/jobs/index.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">คิวงานของช่าง</h2>
  </x-slot>

  @php
    $tab    = $tab    ?? request('tab','my-queue');   // my-queue | unassigned | history
    $q      = $q      ?? request('q','');
    $status = $status ?? request('status','');

    $statusMap = [
      'รอดำเนินการ'   => 'secondary',
      'กำลังดำเนินการ' => 'warning',
      'เสร็จสิ้น'     => 'success',
      'ยกเลิก'        => 'dark',
    ];

    $CLOSED = ['เสร็จสิ้น','ยกเลิก'];   // NEW: สถานะที่ให้ซ่อนออกจาก my-queue / unassigned
  @endphp

  <div class="py-4 container">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-3">
      <li class="nav-item">
        <a class="nav-link @if($tab==='my-queue') active @endif"
           href="{{ route('tech.jobs.index',['tab'=>'my-queue'] + request()->except('page')) }}">
          งานของฉัน (กำลังทำ/รอทำ)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link @if($tab==='unassigned') active @endif"
           href="{{ route('tech.jobs.index',['tab'=>'unassigned'] + request()->except('page')) }}">
          คิวที่ยังไม่ถูกมอบหมาย
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link @if($tab==='history') active @endif"
           href="{{ route('tech.jobs.index',['tab'=>'history'] + request()->except('page')) }}">
          ประวัติของฉัน
        </a>
      </li>
    </ul>

    {{-- Filters --}}
    <form method="GET" action="{{ route('tech.jobs.index') }}" class="row g-2 align-items-end mb-3">
      <input type="hidden" name="tab" value="{{ $tab }}">
      <div class="col-md-5">
        <label class="form-label">ค้นหา</label>
        <input type="search" name="q" value="{{ $q }}" class="form-control"
               placeholder="ที่อยู่อุปกรณ์ / รายการ / รายละเอียด">
      </div>
      <div class="col-md-3">
        <label class="form-label">สถานะ</label>
        <select name="status" class="form-select">
          <option value="">ทุกสถานะ</option>
          @foreach (['รอดำเนินการ','กำลังดำเนินการ','เสร็จสิ้น','ยกเลิก'] as $st)
            <option value="{{ $st }}" @selected($status===$st)>{{ $st }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary w-100">กรองข้อมูล</button>
      </div>
      <div class="col-md-2">
        <a class="btn btn-outline-secondary w-100"
           href="{{ route('tech.jobs.index',['tab'=>$tab]) }}">ล้างค่า</a>
      </div>
    </form>

    {{-- Table --}}
    <div class="card shadow-sm">
      <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th style="width:72px">#</th>
              <th>ที่อยู่อุปกรณ์</th>
              <th>รายการ</th>
              <th style="width:140px">สถานะ</th>
              <th class="text-end" style="width:220px">รายละเอียด</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($reports as $i => $r)
              @php
                // ---- คำนวณค่าใช้แสดงผล ----
                $pmInt   = [1=>'danger', 2=>'warning', 3=>'secondary', 4=>'secondary', 5=>'success'];
                $labelInt= [1=>'สูงสุด', 2=>'สูง', 3=>'ปกติ', 4=>'ต่ำ', 5=>'ต่ำมาก'];
                $pmStr   = ['urgent'=>'danger','high'=>'warning','normal'=>'secondary','low'=>'success'];

                $prio  = $r->priority ?? null;
                $badge = is_numeric($prio) ? ($pmInt[(int)$prio] ?? 'secondary')
                                           : ($pmStr[strtolower((string)$prio)] ?? 'secondary');
                $label = is_numeric($prio) ? ($labelInt[(int)$prio] ?? $prio)
                                           : ( $prio ? ucfirst((string)$prio) : '-' );

                $due = $r->due_at instanceof \Illuminate\Support\Carbon ? $r->due_at
                      : (\Illuminate\Support\Str::of((string)($r->due_at ?? ''))->isNotEmpty()
                          ? \Illuminate\Support\Carbon::parse($r->due_at) : null);

                $stText = $r->status ?? 'รอดำเนินการ';
                $stBadge= $statusMap[$stText] ?? 'secondary';
              @endphp

              {{-- NEW: ซ่อนงานที่ปิดแล้ว (เฉพาะแท็บ my-queue และ unassigned) --}}
              @if ($tab !== 'history' && in_array($stText, $CLOSED, true))
                @continue
              @endif
              {{-- /NEW --}}

              <tr>
                <td>{{ $reports->firstItem() + $i }}</td>
                <td>{{ $r->device_address }}</td>
                <td>{{ $r->device_list }}</td>
                <td><span class="badge bg-{{ $stBadge }}">{{ $stText }}</span></td>
                <td class="text-end">
                  <a href="{{ route('tech.jobs.show', $r->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-zoom-in"></i>
                  </a>

                  @if ($tab === 'unassigned')
                    <form method="POST" action="{{ route('tech.jobs.claim', $r) }}" class="d-inline">
                      @csrf
                      <button class="btn btn-sm btn-outline-primary"
                              onclick="return confirm('ยืนยันรับงานนี้?')">
                        รับงาน
                      </button>
                    </form>
                  @else
                    @if (in_array($stText, ['รอดำเนินการ','กำลังดำเนินการ'], true))
                      <form method="POST" action="{{ route('tech.jobs.start', $r) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-warning">เริ่มงาน</button>
                      </form>
                      <form method="POST" action="{{ route('tech.jobs.complete', $r) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-success">เสร็จงาน</button>
                      </form>
                    @endif
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted">ไม่พบข้อมูล</td>
              </tr>
            @endforelse
          </tbody>
        </table>

        {{ $reports->withQueryString()->links() }}
      </div>
    </div>
  </div>
</x-app-layout>
