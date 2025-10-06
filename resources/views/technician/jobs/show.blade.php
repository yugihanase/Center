<x-app-layout>
  <div class="container py-3">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-semibold">รายละเอียดงาน #{{ $report->id }}</div>

      <div class="card-body">
        <div class="row g-4 align-items-start">
          {{-- ซ้าย: รายละเอียด --}}
          <div class="col-12 col-lg-7">
            <dl class="row mb-0">
              <dt class="col-sm-4 col-lg-5">ที่อยู่อุปกรณ์</dt>
              <dd class="col-sm-8 col-lg-7">{{ $report->device_address }}</dd>

              <dt class="col-sm-4 col-lg-5">รายการ</dt>
              <dd class="col-sm-8 col-lg-7">{{ $report->device_list }}</dd>

              <dt class="col-sm-4 col-lg-5">สถานะ (งาน)</dt>
              <dd class="col-sm-8 col-lg-7">
                @php $map=['รอดำเนินการ'=>'secondary','กำลังดำเนินการ'=>'warning','เสร็จสิ้น'=>'success','ยกเลิก'=>'dark']; @endphp
                <span class="badge bg-{{ $map[$report->status] ?? 'secondary' }}">{{ $report->status }}</span>
              </dd>

              <dt class="col-sm-4 col-lg-5">ผู้แจ้ง</dt>
              <dd class="col-sm-8 col-lg-7">{{ $report->user?->name ?? '-' }}</dd>

              <dt class="col-sm-4 col-lg-5">สร้างเมื่อ</dt>
              <dd class="col-sm-8 col-lg-7">{{ $report->created_at?->format('Y-m-d H:i') }}</dd>

              @php
                $current = $report->currentAssignment;
                $latest  = $report->latestAssignment;
                $techName   = $current?->technician?->name ?? $latest?->technician?->name ?? '— ยังไม่มอบหมาย —';
                $aStatus    = $current?->status ?? $latest?->status;
                $startedAt  = $current?->started_at ?? $latest?->started_at;
                $finishedAt = $current?->finished_at ?? $latest?->finished_at;
              @endphp

              <dt class="col-sm-4 col-lg-5">ผู้รับผิดชอบ</dt>
              <dd class="col-sm-8 col-lg-7">{{ $techName }}</dd>

              @if($aStatus)
                <dt class="col-sm-4 col-lg-5">สถานะ (ผู้รับผิดชอบ)</dt>
                <dd class="col-sm-8 col-lg-7"><span class="badge bg-info">{{ $aStatus }}</span></dd>
              @endif

              <dt class="col-sm-4 col-lg-5">รับงาน</dt>
              <dd class="col-sm-8 col-lg-7">{{ optional($startedAt)->format('Y-m-d H:i') ?? '—' }}</dd>

              <dt class="col-sm-4 col-lg-5">เสร็จสิ้น</dt>
              <dd class="col-sm-8 col-lg-7">{{ optional($finishedAt)->format('Y-m-d H:i') ?? '—' }}</dd>

              <dt class="col-sm-4 col-lg-5">รายละเอียด</dt>
              <dd class="col-sm-8 col-lg-7">{{ $report->detail }}</dd>
            </dl>
          </div>

          {{-- ขวา: แกลเลอรี --}}
          <div class="col-12 col-lg-5">
            <h6 class="fw-semibold mb-2">รูปภาพประกอบ ({{ $report->images?->count() ?? 0 }})</h6>
            @if($report->images?->count())
              <div class="row g-2">
                @foreach($report->images as $i => $img)
                  @php $url = asset('storage/'.$img->path); @endphp
                  <div class="col-6 col-md-4">
                    <div class="border rounded-3 overflow-hidden">
                      <a href="{{ $url }}" target="_blank" rel="noopener">
                        <img src="{{ $url }}" class="img-fluid" alt="{{ $img->original_name }}">
                      </a>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="text-muted">ไม่มีรูปแนบ</div>
            @endif
          </div>
        </div>
      </div>

      {{-- แถบปุ่มการทำงานสำหรับช่าง --}}
      <div class="card-footer d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <a href="{{ route('tech.jobs.index') }}" class="btn btn-outline-secondary">ย้อนกลับ</a>

        <div class="d-flex flex-wrap gap-2">
          {{-- ถ้ายังไม่มี assignment เปิดอยู่ -> ให้กดรับงานได้ --}}
          @if(!$report->currentAssignment)
            <form method="POST" action="{{ route('tech.jobs.claim', $report) }}">
              @csrf
              <button class="btn btn-primary">รับงาน</button>
            </form>
          @endif

          {{-- ถ้ามี assignment เปิดอยู่และเป็นของฉัน -> เริ่ม/ปิดงานได้ --}}
          @if(optional($report->currentAssignment)->technician_id === auth()->id())
            <form method="POST" action="{{ route('tech.jobs.start', $report) }}">
              @csrf
              <button class="btn btn-warning">เริ่มงาน</button>
            </form>
            <form method="POST" action="{{ route('tech.jobs.complete', $report) }}">
              @csrf
              <button class="btn btn-success">ปิดงาน</button>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
