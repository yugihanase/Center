<x-app-layout>
  <div class="container py-3">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-semibold">รายละเอียดงาน #{{ $report->id }}</div>
      {{-- ===== รายละเอียด + แกลเลอรีข้างกัน ===== --}}
      <div class="card-body">
        <div class="row g-4 align-items-start">
          {{-- ซ้าย: รายละเอียด --}}
          <div class="col-12 col-lg-7">
            <dl class="row mb-0">
              <dt class="col-sm-4 col-lg-5">ที่อยู่อุปกรณ์</dt>
              <dd class="col-sm-8 col-lg-7">{{ $report->device_address }}</dd>

              <dt class="col-sm-4 col-lg-5">รายการ</dt>
              <dd class="col-sm-8 col-lg-7">{{ $report->device_list }}</dd>

              <dt class="col-sm-4 col-lg-5">สถานะ</dt>
              <dd class="col-sm-8 col-lg-7">
                @php $map=['รอดำเนินการ'=>'secondary','กำลังดำเนินการ'=>'warning','เสร็จสิ้น'=>'success','ยกเลิก'=>'dark']; @endphp
                <span class="badge bg-{{ $map[$report->status] ?? 'secondary' }}">{{ $report->status }}</span>
              </dd>

              <dt class="col-sm-4 col-lg-5">ผู้แจ้ง</dt>
              <dd class="col-sm-8 col-lg-7">{{ $report->user?->name ?? '-' }}</dd>

              <dt class="col-sm-4 col-lg-5">เมื่อ</dt>
              <dd class="col-sm-8 col-lg-7">{{ $report->created_at?->format('Y-m-d H:i') }}</dd>

              <dt class="col-sm-4 col-lg-5">รายละเอียด</dt>
              <dd class="col-sm-8 col-lg-7">{{ $report->detail }}</dd>
            </dl>
          </div>

          {{-- ขวา: แกลเลอรีรูป --}}
          <div class="col-12 col-lg-5">
            <h6 class="fw-semibold mb-2">
              รูปภาพประกอบ ({{ $report->images?->count() ?? 0 }})
            </h6>

            @if($report->images?->count())
              <div class="row g-2">
                @foreach($report->images as $i => $img)
                  @php $url = asset('storage/'.$img->path); @endphp
                  <div class="col-6 col-md-4">
                    <div class="border rounded-3 overflow-hidden">
                      <a href="{{ $url }}"
                        data-bs-toggle="modal"
                        data-bs-target="#imageModal"
                        data-image="{{ $url }}"
                        data-index="{{ $i }}">
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
      {{-- Modal แสดงรูปขนาดใหญ่ --}}
            <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                  <div class="modal-body p-0">
                    <img id="modalImage" src="" alt="" class="img-fluid w-100">
                  </div>
                  <div class="modal-footer justify-content-between">
                    <div class="d-flex gap-2">
                      <button type="button" class="btn btn-outline-secondary btn-prev">
                        <i class="fas fa-chevron-left"></i>
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-next">
                        <i class="fas fa-chevron-right"></i>
                      </button>
                    </div>
                    <div class="d-flex gap-2">
                      <a id="modalDownload" href="#" download class="btn btn-primary">
                        <i class="fas fa-download me-1"></i> ดาวน์โหลด
                      </a>
                      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
      {{-- ====== รายละเอียดการทำงาน (จริง) ====== --}}
<div class="card-header fw-semibold">รายละเอียดการทำงาน #{{ $report->id }}</div>
<div class="card-body">
  @php
    // เอาช่างที่เปิดอยู่ก่อน ถ้าไม่มีเปิดอยู่แล้ว fallback เป็น assignment ล่าสุด
    $current = $report->currentAssignment;
    $latest  = $report->latestAssignment;

    $techName   = $current?->technician?->name ?? $latest?->technician?->name ?? '— ยังไม่มอบหมาย —';
    $startedAt  = $current?->started_at ?? $latest?->started_at;
    $finishedAt = $current?->finished_at ?? $latest?->finished_at;
    $aStatus    = $current?->status ?? $latest?->status; // ถ้าอยากโชว์สถานะ assignment
  @endphp

  <dl class="row mb-0">
    <dt class="col-sm-3">ผู้รับผิดชอบ</dt>
    <dd class="col-sm-9">{{ $techName }}</dd>

    @if($aStatus)
      <dt class="col-sm-3">สถานะ (ผู้รับผิดชอบ)</dt>
      <dd class="col-sm-9"><span class="badge bg-info">{{ $aStatus }}</span></dd>
    @endif

    <dt class="col-sm-3">รับงาน</dt>
    <dd class="col-sm-9">{{ optional($startedAt)->format('Y-m-d H:i') ?? '—' }}</dd>

    <dt class="col-sm-3">เสร็จสิ้น</dt>
    <dd class="col-sm-9">{{ optional($finishedAt)->format('Y-m-d H:i') ?? '—' }}</dd>

    <dt class="col-sm-3">รายละเอียด</dt>
    <dd class="col-sm-9">{{ $report->work_detail ?? '—' }}</dd>
  </dl>
</div>


      <div class="card-footer d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">ย้อนกลับ</a>
        @can('manage-reports')
          <div class="d-flex flex-wrap gap-2 align-items-center">

            {{-- ฟอร์มเปลี่ยนสถานะ (แอดมิน) --}}
            <form method="POST"
                  action="{{ route('admin.reports.updateStatus', $report) }}"
                  class="d-flex align-items-center gap-2">
              @csrf
              @method('PATCH')

              <label for="status" class="mb-0 small text-muted">เปลี่ยนสถานะ</label>

              <div class="input-group input-group-sm" style="width:auto">
                <select id="status" name="status" class="form-select form-select-sm">
                  @foreach (['รอดำเนินการ','กำลังดำเนินการ','เสร็จสิ้น','ยกเลิก'] as $st)
                    <option value="{{ $st }}" @selected($report->status === $st)>{{ $st }}</option>
                  @endforeach
                </select>
                <button type="submit" class="btn btn-success">บันทึก</button>
              </div>
            </form>

            {{-- ฟอร์มลบ (แอดมิน) --}}
            <form method="POST"
                  action="{{ route('admin.reports.destroy', $report) }}"
                  class="d-inline"
                  onsubmit="return confirm('ยืนยันการลบรายการนี้?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-outline-danger btn-sm">ลบ</button>
            </form>

          </div>
        @endcan
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const modalEl = document.getElementById('imageModal');
      const modalImg = document.getElementById('modalImage');
      const modalDl  = document.getElementById('modalDownload');

      // เก็บลิสต์ URL ทั้งหมดเพื่อกดซ้าย/ขวา
      const urls = Array.from(document.querySelectorAll('[data-bs-target="#imageModal"]'))
                        .map(a => a.getAttribute('data-image'));
      let idx = 0;

      function showAt(i) {
        idx = (i + urls.length) % urls.length;
        const src = urls[idx];
        modalImg.src = src;
        modalDl.href = src;
      }

      // เปิดจากรูปย่อ
      document.querySelectorAll('[data-bs-target="#imageModal"]').forEach(a => {
        a.addEventListener('click', (e) => {
          const start = parseInt(a.getAttribute('data-index') || '0', 10);
          showAt(start);
        });
      });

      // ปุ่มในโมดัล
      modalEl.querySelector('.btn-prev').addEventListener('click', () => showAt(idx - 1));
      modalEl.querySelector('.btn-next').addEventListener('click', () => showAt(idx + 1));

      // ปุ่มลูกศรคีย์บอร์ด
      modalEl.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft')  showAt(idx - 1);
        if (e.key === 'ArrowRight') showAt(idx + 1);
      });

      // โฟกัสรับคีย์เมื่อเปิด
      modalEl.addEventListener('shown.bs.modal', () => modalEl.focus());
    });
  </script>
</x-app-layout>