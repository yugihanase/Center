<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('ระบบแจ้งซ่อมอุปกรณ์') }}
    </h2>
  </x-slot>

  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow" style="width: 90%; max-width: 1280px;">
      <div class="card-header text-center fw-semibold">
        แจ้งปัญหา & ติดตามสถานะ
      </div>

      <div class="card-body">
        @if (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- ===== GRID LAYOUT ===== --}}
        <div class="row g-4 align-items-start">

          {{-- ฟอร์มแจ้งซ่อม --}}
          <div class="col-lg-4">
            <div class="card shadow-sm h-100">
              <div class="card-header fw-semibold text-center">แจ้งปัญหา/คำขอ</div>
              <div class="card-body">
                <form method="POST" action="{{ route('report.store') }}" enctype="multipart/form-data">
                  @csrf

                  <div class="mb-3">
                    <label for="device_address" class="form-label">สถานที่</label>
                    <input type="text" id="device_address" name="device_address"
                      class="form-control @error('device_address') is-invalid @enderror"
                      value="{{ old('device_address') }}" required>
                    @error('device_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>

                  <div class="mb-3">
                    <label for="device_list" class="form-label">รายการอุปกรณ์</label>
                    <input type="text" id="device_list" name="device_list"
                      class="form-control @error('device_list') is-invalid @enderror"
                      value="{{ old('device_list') }}" required>
                    @error('device_list') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>

                  <div class="mb-3">
                    <label for="detail" class="form-label">แจ้งรายละเอียด</label>
                    <textarea id="detail" name="detail" rows="3"
                      class="form-control @error('detail') is-invalid @enderror"
                      required>{{ old('detail') }}</textarea>
                    @error('detail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>

                  {{-- หลายรูป --}}
                  <div class="mb-3">
                    <label class="form-label fw-semibold">อัปโหลดรูปภาพ (หลายไฟล์)</label>

                    <div id="multiInfo" class="small text-muted mb-2">ยังไม่ได้เลือกรูป</div>

                    {{-- พรีวิว --}}
                    <div id="gallery" class="row g-2 mb-2"></div>

                    <div class="d-flex gap-2 mb-1">
                      <button type="button" class="btn btn-outline-secondary" id="pickImagesBtn">
                        <i class="fas fa-images me-1"></i> เลือกรูป
                      </button>
                      <button type="button" class="btn btn-outline-danger" id="clearAllBtn">
                        ลบทั้งหมด
                      </button>
                    </div>

                    {{-- input จริง (ซ่อน) --}}
                    <input
                      type="file"
                      id="images"
                      name="images[]"
                      class="visually-hidden @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                      accept="image/*"
                      multiple
                    >

                    @error('images')   <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    @error('images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                    <div class="form-text">
                      รองรับ JPEG/PNG/WebP ฯลฯ, ไฟล์ละ ≤ 2 MB, รวมไม่เกิน 10 รูป
                    </div>

                    <div id="multiError" class="text-danger small mt-1 d-none"></div>
                  </div>

                  <button type="submit" class="btn btn-primary w-100">ยืนยัน</button>
                </form>
              </div>
            </div>
          </div>

          {{-- ตารางติดตาม --}}
          <div class="col-lg-8">
            <div class="card shadow-sm h-100">
              <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <span class="fw-semibold">ติดตามรายการ</span>

                <form method="GET" action="{{ route('report.follow') }}" class="d-flex gap-2 flex-grow-1" id="filterForm">
                  <select class="form-select w-auto" name="status" id="statusSelect" onchange="handleStatusChange(this)">
                    <option value="" @selected(($status ?? '') === '')>ทุกสถานะ</option>
                    @foreach (['รอดำเนินการ','กำลังดำเนินการ','เสร็จสิ้น','ยกเลิก'] as $st)
                      <option value="{{ $st }}" @selected(($status ?? '') === $st)>{{ $st }}</option>
                    @endforeach
                  </select>

                  <input type="search" class="form-control" name="q" value="{{ $q }}"
                         placeholder="ค้นหา ชื่อ/อีเมล/ที่อยู่/รายการ/รายละเอียด" id="qInput">

                  <button class="btn btn-outline-primary" type="submit">ค้นหา</button>
                </form>

                <script>
                  function handleStatusChange(selectEl) {
                    if (selectEl.value === '') {
                      window.location.href = '{{ route('report.follow') }}';
                    } else {
                      selectEl.form.submit();
                    }
                  }
                </script>
              </div>

              <div class="card-body">
                <div class="table-responsive" style="max-height: 480px;">
                  <table class="table table-striped align-middle text-center mb-0">
                    <thead class="position-sticky top-0 bg-white">
                      <tr>
                        <th>#</th>
                        <th>สถานที่</th>
                        <th>รายการ</th>
                        <th style="min-width:260px;">รายละเอียด</th>
                        <th>สถานะ</th>
                        <th>ผู้แจ้ง</th>
                        <th>เมื่อ</th>
                        <th>ติดตาม</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($reports as $i => $r)
                        <tr>
                          <td>{{ $reports->firstItem() + $i }}</td>
                          <td>{{ $r->device_address }}</td>
                          <td>{{ $r->device_list }}</td>
                          <td class="text-truncate" style="max-width: 260px;">
                            {{ \Illuminate\Support\Str::limit($r->detail, 100) }}
                          </td>
                          <td>
                            @php
                              $map = [
                                'รอดำเนินการ'   => 'secondary',
                                'กำลังดำเนินการ' => 'warning',
                                'เสร็จสิ้น'     => 'success',
                                'ยกเลิก'         => 'dark',
                              ];
                            @endphp
                            <span class="badge bg-{{ $map[$r->status] ?? 'secondary' }}">{{ $r->status }}</span>
                          </td>
                          <td>{{ $r->user?->name ?? '-' }}</td>
                          <td class="text-nowrap">{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
                          <td>
                            <a href="{{ route('report.show', $r) }}" class="btn btn-sm btn-outline-primary">
                              <i class="bi bi-plus-circle"></i>
                            </a>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="7" class="text-center text-muted">ไม่พบรายการ</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                  {{ $reports->onEachSide(1)->links() }}
                </div>
              </div>
            </div>
          </div>
        </div> {{-- /.row --}}
      </div> {{-- /.card-body --}}
    </div>
  </div>

  {{-- สคริปต์จัดการอัปโหลดหลายรูป (เดิม) --}}
  <script>
  (() => {
    const input   = document.getElementById('images');
    const pickBtn = document.getElementById('pickImagesBtn');
    const clrBtn  = document.getElementById('clearAllBtn');
    const gallery = document.getElementById('gallery');
    const info    = document.getElementById('multiInfo');
    const errBox  = document.getElementById('multiError');

    const MAX_MB    = 2;
    const MAX_FILES = 10;

    let filesArr = [];
    let urls = [];

    function bytesToMB(b){ return (b / (1024*1024)); }
    function fmtMB(x){ return `${x.toFixed(2)} MB`; }
    function showError(msg){ errBox.textContent = msg; errBox.classList.remove('d-none'); }
    function clearError(){ errBox.classList.add('d-none'); errBox.textContent = ''; }

    function updateInfo(){
      const count = filesArr.length;
      const totalMB = bytesToMB(filesArr.reduce((s,f)=>s+f.size,0));
      info.textContent = count ? `เลือกรูปแล้ว ${count} ไฟล์ • รวม ${fmtMB(totalMB)}` : 'ยังไม่ได้เลือกรูป';
    }

    function syncToInput(){
      const dt = new DataTransfer();
      filesArr.forEach(f => dt.items.add(f));
      input.files = dt.files;
    }

    function revokeAll(){ urls.forEach(u => URL.revokeObjectURL(u)); urls = []; }

    function render(){
      revokeAll();
      gallery.innerHTML = '';
      filesArr.forEach((f, idx) => {
        const url = URL.createObjectURL(f); urls.push(url);
        const col = document.createElement('div');
        col.className = 'col-6 col-sm-4 col-md-3 col-lg-3';
        col.innerHTML = `
          <div class="position-relative border rounded-3 overflow-hidden">
            <img src="${url}" class="img-fluid" alt="">
            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                    data-index="${idx}" title="ลบรูปนี้">×</button>
          </div>
          <div class="small text-truncate mt-1" title="${f.name}">${f.name}</div>
        `;
        gallery.appendChild(col);
      });
      gallery.querySelectorAll('button[data-index]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const i = +e.currentTarget.dataset.index;
          filesArr.splice(i, 1); syncToInput(); updateInfo(); render();
        });
      });
    }

    function resetAll(){
      filesArr = []; input.value = ''; revokeAll(); gallery.innerHTML = '';
      updateInfo(); clearError();
    }

    pickBtn?.addEventListener('click', () => input?.click());
    clrBtn?.addEventListener('click', resetAll);

    input?.addEventListener('change', () => {
      clearError();
      const incoming = Array.from(input.files || []);
      if (!incoming.length) return;
      let combined = filesArr.slice();

      for (const f of incoming) {
        if (!f.type || !f.type.startsWith('image/')) { showError(`ไฟล์ "${f.name}" ไม่ใช่รูปภาพ`); continue; }
        const sizeMB = bytesToMB(f.size);
        if (sizeMB > MAX_MB) { showError(`ไฟล์ "${f.name}" ใหญ่เกินไป (${fmtMB(sizeMB)}) — จำกัด ${MAX_MB} MB`); continue; }
        if (combined.length >= MAX_FILES) { showError(`เลือกรูปเกินจำนวนที่กำหนด (${MAX_FILES} ไฟล์)`); break; }
        const dup = combined.some(x => x.name === f.name && x.size === f.size && x.lastModified === f.lastModified);
        if (dup) continue;
        combined.push(f);
      }

      filesArr = combined;
      syncToInput(); updateInfo(); render();
    });

    updateInfo();
  })();
  </script>
</x-app-layout>
