{{-- resources/views/admin/jobs/assign.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">ศูนย์จ่ายงาน</h2>
  </x-slot>

  <div class="container-fluid py-3">
    <div class="row g-3">

      {{-- ซ้าย: คิวงานที่ยังไม่มอบหมาย --}}
      <div class="col-lg-7">
        <div class="card shadow-sm">
          <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
            <span>คิวเข้ามา (ยังไม่มอบหมาย)</span>
            <small class="text-muted">แสดงเฉพาะงานที่ยังไม่ปิด</small>
          </div>

          <div class="card-body">
            <form id="bulkForm" class="mb-2">
              @csrf
              <div class="d-flex flex-wrap gap-2">
                <select id="bulkTech" class="form-select form-select-sm" style="max-width:260px">
                  <option value="">— เลือกช่าง —</option>
                  @foreach($techs as $t)
                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->open_jobs_count }} งาน)</option>
                  @endforeach
                </select>
                <button type="button" class="btn btn-primary btn-sm" onclick="bulkAssign()">
                  มอบหมายที่เลือก
                </button>
              </div>
            </form>

            @php $CLOSED = ['เสร็จสิ้น','ยกเลิก']; @endphp

            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:36px"><input type="checkbox" onclick="toggleAll(this)" aria-label="เลือกทั้งหมด"></th>
                    <th style="width:52px">#</th>
                    <th>ที่อยู่อุปกรณ์</th>
                    <th>รายการ</th>
                    <th>ผู้แจ้ง</th>
                    <th style="width:150px">เมื่อ</th>
                    <th style="width:260px">มอบหมาย</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($unassigned as $i => $r)
                    @continue(in_array($r->status ?? '', $CLOSED, true))
                    <tr>
                      <td><input type="checkbox" class="rowChk" value="{{ $r->id }}"></td>
                      <td>{{ $unassigned->firstItem() + $i }}</td>
                      <td class="text-truncate" style="max-width:220px">{{ $r->device_address }}</td>
                      <td class="text-truncate" style="max-width:180px">{{ $r->device_list }}</td>
                      <td>{{ $r->requester_name ?? ($r->user?->name ?? '-') }}</td>
                      <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                      <td>
                        <div class="input-group input-group-sm">
                          <select class="form-select form-select-sm" id="tech-{{ $r->id }}">
                            <option value="">เลือกช่าง…</option>
                            @foreach($techs as $t)
                              <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->open_jobs_count }})</option>
                            @endforeach
                          </select>
                          {{-- สำคัญ: ส่ง this เข้าไปเพื่อกัน error event undefined --}}
                          <button type="button" class="btn btn-outline-primary" onclick="assign({{ $r->id }}, this)">
                            มอบหมาย
                          </button>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="7" class="text-center text-muted">ไม่มีงานรอมอบหมาย</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="pt-2">
              {{ $unassigned->withQueryString()->links() }}
            </div>
          </div>
        </div>
      </div>

      {{-- ขวา: รายชื่อช่าง + งานเปิดของช่างที่เลือก --}}
      <div class="col-lg-5">

        {{-- รายชื่อช่างทั้งหมด --}}
        <div class="card shadow-sm mb-3">
          <div class="card-header fw-semibold">ช่างทั้งหมด</div>
          <div class="card-body p-0">
            <ul class="list-group list-group-flush">
              @foreach($techs as $t)
                @php
                  $active = ($viewTechId ?? 0) === $t->id;
                  // ใช้ fullUrlWithQuery เพื่อสร้างลิงก์ ?view_tech=...
                  $url = request()->fullUrlWithQuery([
                    'view_tech' => $t->id,
                    'page'      => null,     // รีเซ็ตเพจฝั่งซ้าย
                    'tech_page' => null,     // รีเซ็ตเพจฝั่งขวา
                  ]);
                @endphp

                <li class="list-group-item d-flex justify-content-between align-items-center @if($active) bg-light @endif" style="position:relative">
                  <a href="{{ $url }}" class="stretched-link text-decoration-none @if($active) fw-semibold text-primary @else text-body @endif">
                    <div>{{ $t->name }}</div>
                    <small class="text-muted">{{ $t->email }}</small>
                  </a>
                  <span class="badge @if($active) bg-primary @else bg-secondary @endif rounded-pill">
                    {{ $t->open_jobs_count }} งานเปิด
                  </span>
                </li>
              @endforeach
            </ul>
          </div>
        </div>

        {{-- ตารางงานเปิดของช่างที่ถูกคลิก --}}
        <div class="card shadow-sm">
          <div class="card-header fw-semibold">
            @if(!empty($selectedTech))
              งานที่เปิดอยู่ของ: <span class="text-primary">{{ $selectedTech->name }}</span>
            @else
              คลิกรายชื่อช่างด้านบนเพื่อดูงานที่เปิดอยู่
            @endif
          </div>

          @if(!empty($selectedTech))
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th style="width:52px">#</th>
                      <th>ที่อยู่อุปกรณ์</th>
                      <th>รายการ</th>
                      <th style="width:120px">สถานะ</th>
                      <th class="text-end" style="width:90px">จัดการ</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($techOpen as $idx => $a)
                      @php
                        $r = $a->report;
                        $st = $a->status;
                        $badge = $st === 'กำลังดำเนินการ' ? 'warning' : 'secondary';
                      @endphp
                      <tr>
                        <td>{{ $techOpen->firstItem() + $idx }}</td>
                        <td class="text-truncate" style="max-width:200px">{{ $r?->device_address ?? '-' }}</td>
                        <td class="text-truncate" style="max-width:180px">{{ $r?->device_list ?? '-' }}</td>
                        <td><span class="badge bg-{{ $badge }}">{{ $st }}</span></td>
                        <td class="text-end">
                          <a href="{{ route('tech.jobs.show', $r?->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-zoom-in"></i>
                          </a>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="5" class="text-center text-muted">ไม่มีงานเปิดอยู่</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              {{-- คงพารามิเตอร์เมื่อเปลี่ยนหน้า (ต้องคง view_tech ด้วย) --}}
              <div class="p-2">
                {{ $techOpen->appends(request()->except('tech_page') + ['view_tech' => $viewTechId])->links() }}
              </div>
            </div>
          @endif
        </div>

      </div>
    </div>
  </div>

  <script>
    async function postJson(url, payload) {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify(payload)
      });
      const raw = await res.text();
      let data = null;
      try { data = raw ? JSON.parse(raw) : null; } catch {}
      if (!res.ok) {
        let msg = (data && (data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : ''))) || '';
        if (!msg) {
          const m = raw.match(/<title[^>]*>(.*?)<\/title>/i);
          msg = m ? m[1] : raw.slice(0, 200);
        }
        throw new Error(msg || `HTTP ${res.status}`);
      }
      return data ?? {};
    }

    async function assign(reportId, btn){
      const techId = document.getElementById('tech-'+reportId)?.value;
      if(!techId) return alert('เลือกช่างก่อน');
      try {
        if (btn) btn.disabled = true;
        await postJson(`{{ route('admin.jobs.assign.store') }}`, {
          report_id: reportId,
          technician_id: techId
        });
        location.reload();
      } catch(e) {
        alert(e.message || 'มอบหมายไม่สำเร็จ');
      } finally {
        if (btn) btn.disabled = false;
      }
    }

    async function bulkAssign(){
      const techId = document.getElementById('bulkTech')?.value;
      if(!techId) return alert('เลือกช่างก่อน');
      const ids = [...document.querySelectorAll('.rowChk:checked')].map(el => +el.value);
      if(ids.length===0) return alert('เลือกงานอย่างน้อย 1 รายการ');
      try {
        await postJson(`{{ route('admin.jobs.bulk') }}`, {
          report_ids: ids,
          technician_id: techId
        });
        location.reload();
      } catch(e) {
        alert(e.message || 'มอบหมายหลายงานไม่สำเร็จ');
      }
    }

    function toggleAll(master){
      document.querySelectorAll('.rowChk').forEach(ch => ch.checked = master.checked);
    }
  </script>
</x-app-layout>
