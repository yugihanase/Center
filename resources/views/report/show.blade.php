{{-- resources/views/report/show.blade.php --}}
<x-app-layout>
  @php
    $isOwner = auth()->id() === $report->user_id;
    $isAdmin = auth()->user()?->role === 'admin';
    $locked  = in_array($report->status, ['เสร็จสิ้น','ยกเลิก'], true);

    $statusColor = [
      'รอดำเนินการ'    => 'secondary',
      'กำลังดำเนินการ'  => 'warning',
      'เสร็จสิ้น'       => 'success',
      'ยกเลิก'          => 'dark',
    ];

    // งานมอบหมายล่าสุด/ปัจจุบัน
    $current   = $report->currentAssignment;
    $latest    = $report->latestAssignment;
    $techName  = $current?->technician?->name ?? $latest?->technician?->name ?? '— ยังไม่มอบหมาย —';
    $startedAt = $current?->started_at ?? $latest?->started_at;
    $finishedAt= $current?->finished_at ?? $latest?->finished_at;
    $aStatus   = $current?->status ?? $latest?->status;
  @endphp

  <div class="container py-3">
    <div class="card border-0 shadow-sm">

      {{-- ===== Header ===== --}}
      <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="fw-semibold">รายละเอียดงาน #{{ $report->id }}</span>
          <button class="btn btn-light btn-sm px-2 py-1" data-bs-toggle="tooltip" title="คัดลอกหมายเลขงาน" id="btn-copy-id">
            <i class="far fa-copy"></i>
          </button>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="badge bg-{{ $statusColor[$report->status] ?? 'secondary' }}">{{ $report->status }}</span>
          <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> ย้อนกลับ
          </a>
        </div>
      </div>

      {{-- ===== เนื้อหา 2 คอลัมน์ ===== --}}
      <div class="card-body">
        <div class="row g-4 align-items-start">
          {{-- ซ้าย: รายละเอียดหลัก + การมอบหมาย + อินไลน์แก้ไขรายละเอียด --}}
          <div class="col-12 col-lg-7">
            {{-- รายละเอียดหลัก --}}
            <div class="mb-4">
              <dl class="row mb-0 small">
                <dt class="col-sm-4 col-lg-5 text-muted">ที่อยู่อุปกรณ์</dt>
                <dd class="col-sm-8 col-lg-7 d-flex align-items-start gap-2">
                  <span id="device-address">{{ $report->device_address }}</span>
                  <button class="btn btn-outline-secondary btn-xs px-2 py-1" id="btn-copy-address" data-bs-toggle="tooltip" title="คัดลอกที่อยู่อุปกรณ์">
                    <i class="far fa-copy"></i>
                  </button>
                </dd>

                <dt class="col-sm-4 col-lg-5 text-muted">รายการ</dt>
                <dd class="col-sm-8 col-lg-7">{{ $report->device_list }}</dd>

                <dt class="col-sm-4 col-lg-5 text-muted">ผู้แจ้ง</dt>
                <dd class="col-sm-8 col-lg-7">{{ $report->user?->name ?? '-' }}</dd>

                <dt class="col-sm-4 col-lg-5 text-muted">เมื่อ</dt>
                <dd class="col-sm-8 col-lg-7">{{ $report->created_at?->format('Y-m-d H:i') }}</dd>
              </dl>
            </div>

            {{-- รายละเอียดการทำงาน (จริง) --}}
            <div class="mb-4 p-3 rounded-3 bg-light-subtle">
              <h6 class="fw-semibold mb-3">รายละเอียดการทำงาน</h6>
              <dl class="row mb-0 small">
                <dt class="col-sm-4 text-muted">ผู้รับผิดชอบ</dt>
                <dd class="col-sm-8">{{ $techName }}</dd>

                @if($aStatus)
                  <dt class="col-sm-4 text-muted">สถานะ (ผู้รับผิดชอบ)</dt>
                  <dd class="col-sm-8"><span class="badge bg-info">{{ $aStatus }}</span></dd>
                @endif

                <dt class="col-sm-4 text-muted">รับงาน</dt>
                <dd class="col-sm-8">{{ optional($startedAt)->format('Y-m-d H:i') ?? '—' }}</dd>

                <dt class="col-sm-4 text-muted">เสร็จสิ้น</dt>
                <dd class="col-sm-8">{{ optional($finishedAt)->format('Y-m-d H:i') ?? '—' }}</dd>
              </dl>
            </div>

            {{-- อินไลน์แก้ไข: รายละเอียด (เหลือก้อนเดียว ไม่ทำซ้ำในตาราง) --}}
            <div class="mb-2">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="fw-semibold mb-0">รายละเอียด</h6>
                <small class="text-muted">
                  @if(!$isOwner)
                    (อ่านอย่างเดียว: ผู้สร้างเท่านั้นที่แก้ไขได้)
                  @elseif($locked)
                    (อ่านอย่างเดียว: รายการถูก {{ $report->status }})
                  @endif
                </small>
              </div>

              {{-- โหมดแสดงผล --}}
              <div id="detail-display" class="small" style="white-space: pre-wrap;">{{ $report->detail ?: '—' }}</div>

              {{-- โหมดแก้ไข --}}
              <form id="detail-form"
                    method="POST"
                    action="{{ route('report.updateDetail', $report) }}"
                    class="d-none mt-2 needs-disable-on-submit">
                @csrf
                @method('PATCH')
                <textarea
                  id="detail-input"
                  class="form-control @error('detail') is-invalid @enderror js-autosize"
                  name="detail"
                  rows="4"
                  style="white-space: pre-wrap; resize: vertical;"
                  {{ (!$isOwner || $locked) ? 'disabled' : '' }}
                >{{ old('detail', $report->detail) }}</textarea>
                @error('detail')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <div class="mt-2 d-flex flex-wrap gap-2">
                  <button type="submit" class="btn btn-success btn-sm" {{ (!$isOwner || $locked) ? 'disabled' : '' }}>
                    <i class="fa fa-save me-1"></i> บันทึก
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-cancel-edit">ยกเลิก</button>
                  <button type="button" class="btn btn-outline-info btn-sm" id="btn-insert-timestamp"
                          {{ (!$isOwner || $locked) ? 'disabled' : '' }}
                          data-bs-toggle="tooltip" title="แทรกเวลา (สำหรับจดบันทึกขั้นตอน)">
                    <i class="fa fa-clock me-1"></i> เวลา
                  </button>
                </div>

                <input type="hidden" name="form" value="update-detail">
              </form>

              {{-- ปุ่มแก้ไข --}}
              <div id="detail-actions" class="mt-2">
                <button type="button"
                        class="btn btn-outline-primary btn-sm"
                        id="btn-edit-detail"
                        {{ (!$isOwner || $locked) ? 'disabled' : '' }}
                        data-bs-toggle="tooltip"
                        title="{{ (!$isOwner) ? 'ผู้สร้างเท่านั้นที่แก้ไขได้' : ($locked ? 'แก้ไขไม่ได้เพราะปิดงานแล้ว' : 'แก้ไขรายละเอียด') }}">
                  <i class="fa fa-pen me-1"></i> แก้ไข
                </button>
              </div>

              @if (session('success_detail'))
                <div class="alert alert-success py-2 mt-3 mb-0">{{ session('success_detail') }}</div>
              @endif
              @if ($errors->any() && session('form') === 'update-detail')
                <div class="alert alert-danger py-2 mt-2 mb-0">โปรดตรวจสอบข้อมูลที่กรอก</div>
              @endif
            </div>
          </div>

          {{-- ขวา: แกลเลอรี + อัปโหลดเพิ่มเติม (ที่เดียว) --}}
          <div class="col-12 col-lg-5">
            <div class="d-flex align-items-center justify-content-between">
              <h6 class="fw-semibold mb-2 mb-lg-3">รูปภาพประกอบ</h6>
              <span class="badge rounded-pill bg-light text-dark">{{ $report->images?->count() ?? 0 }}</span>
            </div>

            @if($report->images?->count())
              <div class="row g-2" id="galleryGrid">
                @foreach($report->images as $i => $img)
                  @php $url = asset('storage/'.$img->path); @endphp
                  <div class="col-6 col-md-4">
                    <div class="position-relative">
                      <a href="{{ $url }}"
                         class="gallery-thumb d-block rounded-3 overflow-hidden border"
                         data-bs-toggle="modal"
                         data-bs-target="#imageModal"
                         data-image="{{ $url }}"
                         data-index="{{ $i }}"
                         title="{{ $img->original_name }}">
                        <div class="ratio ratio-4x3">
                          <img src="{{ $url }}" alt="{{ $img->original_name }}" loading="lazy" class="img-cover">
                        </div>
                      </a>

                      @if(($isOwner || $isAdmin) && !$locked)
                        <form method="POST" action="{{ route('report.images.destroy', $img) }}"
                              class="position-absolute top-0 end-0 m-1"
                              onsubmit="return confirm('ยืนยันลบรูปนี้?')">
                          @csrf @method('DELETE')
                          <button class="btn btn-sm btn-danger opacity-90" data-bs-toggle="tooltip" title="ลบรูปนี้">
                            <i class="fa fa-trash"></i>
                          </button>
                        </form>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="text-muted small">ไม่มีรูปแนบ</div>
            @endif

            @if(($isOwner || $isAdmin) && !$locked)
              <div class="card mt-3 border-0 shadow-sm">
                <div class="card-header fw-semibold">อัปโหลดรูปเพิ่มเติม</div>
                <div class="card-body">
                  <form method="POST" action="{{ route('report.images.store', $report) }}"
                        enctype="multipart/form-data" id="uploadForm" class="needs-disable-on-submit">
                    @csrf

                    <div id="dropzone" class="p-3 border rounded-3 text-center mb-2" style="background:#fcfcfd">
                      <i class="fa fa-images fa-2x mb-2 d-block"></i>
                      <div class="mb-1">ลากรูปมาวางที่นี่ หรือ</div>
                      <label class="btn btn-outline-primary btn-sm mb-2">
                        เลือกรูป…
                        <input type="file" name="images[]" id="imageInput" class="d-none"
                               accept="image/png,image/jpeg,image/webp" multiple>
                      </label>
                      <div class="text-muted small">รองรับ JPEG/PNG/WebP สูงสุด 10 รูป รูปละ ≤ 2MB</div>
                    </div>

                    <div id="previewGrid" class="row g-2 mb-2 d-none"></div>

                    <div class="d-flex justify-content-between align-items-center">
                      <div class="small text-muted" id="fileCounter">ยังไม่เลือกรูป</div>
                      <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClear" disabled>ล้างรายการ</button>
                        <button type="submit" class="btn btn-success btn-sm" id="btnUpload" disabled>
                          <i class="fa fa-upload me-1"></i> อัปโหลด
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- ===== Footer ===== --}}
      <div class="card-footer d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
          <i class="fa fa-arrow-left me-1"></i> ย้อนกลับ
        </a>
      </div>
    </div>
  </div>

  {{-- ===== Modal แสดงรูปขนาดใหญ่ ===== --}}
  <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-body p-0 position-relative">
          <img id="modalImage" src="" alt="" class="img-fluid w-100">
        </div>
        <div class="modal-footer justify-content-between">
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-prev" aria-label="รูปก่อนหน้า">
              <i class="fas fa-chevron-left"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-next" aria-label="รูปรถัดไป">
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

  {{-- ===== Styles เฉพาะหน้า ===== --}}
  <style>
    .btn-xs { padding: .15rem .4rem; font-size: .75rem; line-height: 1; border-radius: .25rem; }
    .ratio { position: relative; width: 100%; }
    .ratio::before { display: block; padding-top: calc(100% / (4/3)); content: ""; } /* 4:3 */
    .ratio > img.img-cover { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .bg-light-subtle { background: #f8f9fa; }
    .thumb .remove { position: absolute; top: .25rem; right: .25rem; }
    #dropzone.dragover { background: #f1f7ff; border-color: #b6d4fe; }
  </style>

  {{-- ===== Scripts เฉพาะหน้า ===== --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Bootstrap tooltip
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

      // ===== Copy helpers =====
      function copyText(text) {
        navigator.clipboard?.writeText(text);
      }
      document.getElementById('btn-copy-id')?.addEventListener('click', () => copyText(String({{ $report->id }})));
      document.getElementById('btn-copy-address')?.addEventListener('click', () => {
        const t = document.getElementById('device-address')?.textContent?.trim() || '';
        copyText(t);
      });

      // ===== Gallery / Modal =====
      const modalEl = document.getElementById('imageModal');
      const modalImg = document.getElementById('modalImage');
      const modalDl  = document.getElementById('modalDownload');
      const urls = Array.from(document.querySelectorAll('[data-bs-target="#imageModal"]'))
                        .map(a => a.getAttribute('data-image'));
      let idx = 0;

      function showAt(i) {
        if (!urls.length) return;
        idx = (i + urls.length) % urls.length;
        const src = urls[idx];
        modalImg.src = src;
        modalDl.href = src;
      }
      document.querySelectorAll('[data-bs-target="#imageModal"]').forEach(a => {
        a.addEventListener('click', () => {
          const start = parseInt(a.getAttribute('data-index') || '0', 10);
          showAt(start);
        });
      });
      modalEl?.querySelector('.btn-prev')?.addEventListener('click', () => showAt(idx - 1));
      modalEl?.querySelector('.btn-next')?.addEventListener('click', () => showAt(idx + 1));
      modalEl?.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft')  showAt(idx - 1);
        if (e.key === 'ArrowRight') showAt(idx + 1);
      });
      modalEl?.addEventListener('shown.bs.modal', () => modalEl.focus());

      // ===== Inline edit: รายละเอียด =====
      const display   = document.getElementById('detail-display');
      const form      = document.getElementById('detail-form');
      const input     = document.getElementById('detail-input');
      const btnEdit   = document.getElementById('btn-edit-detail');
      const btnCancel = document.getElementById('btn-cancel-edit');
      const actions   = document.getElementById('detail-actions');
      const btnTime   = document.getElementById('btn-insert-timestamp');

      function autosize(el) {
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight + 2) + 'px';
      }
      if (input) {
        autosize(input);
        input.addEventListener('input', () => autosize(input));
      }
      function enterEdit() {
        display?.classList.add('d-none');
        actions?.classList.add('d-none');
        form?.classList.remove('d-none');
        setTimeout(() => input?.focus(), 0);
      }
      function exitEdit() {
        form?.classList.add('d-none');
        display?.classList.remove('d-none');
        actions?.classList.remove('d-none');
        if (input && display) {
          input.value = display.textContent.trim() === '—' ? '' : display.textContent.trim();
          autosize(input);
        }
      }
      document.querySelectorAll('form.needs-disable-on-submit').forEach(f => {
        f.addEventListener('submit', () => {
          f.querySelectorAll('button[type="submit"]').forEach(b => b.disabled = true);
        });
      });
      btnEdit?.addEventListener('click', enterEdit);
      btnCancel?.addEventListener('click', exitEdit);
      input?.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') form?.submit();
        if (e.key === 'Escape') exitEdit();
      });
      btnTime?.addEventListener('click', () => {
        if (!input) return;
        const now = new Date();
        const pad = n => String(n).padStart(2,'0');
        const stamp = `[${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}] `;
        const caret = input.selectionStart ?? input.value.length;
        input.value = input.value.slice(0, caret) + stamp + input.value.slice(caret);
        autosize(input);
        input.focus();
        input.setSelectionRange(caret + stamp.length, caret + stamp.length);
      });
      const shouldEdit = "{{ session('form') === 'update-detail' && $errors->any() ? '1' : '' }}";
      if (shouldEdit) enterEdit();

      // ===== Dropzone & Preview =====
      const dz        = document.getElementById('dropzone');
      const fileInput = document.getElementById('imageInput');
      const grid      = document.getElementById('previewGrid');
      const btnClear  = document.getElementById('btnClear');
      const btnUpload = document.getElementById('btnUpload');
      const counter   = document.getElementById('fileCounter');
      const upForm    = document.getElementById('uploadForm');

      if (dz && fileInput && grid && upForm) {
        const MAX_FILES = 10;
        const MAX_SIZE  = 2 * 1024 * 1024; // 2MB
        let files = [];

        function refreshUI() {
          grid.innerHTML = '';
          if (files.length === 0) {
            grid.classList.add('d-none');
            btnClear.disabled  = true;
            btnUpload.disabled = true;
            counter.textContent = 'ยังไม่เลือกรูป';
            return;
          }
          grid.classList.remove('d-none');
          btnClear.disabled  = false;
          btnUpload.disabled = false;
          counter.textContent = `เลือกรูปแล้ว ${files.length} / ${MAX_FILES} รูป`;
          files.forEach((file, idx) => {
            const url = URL.createObjectURL(file);
            const col = document.createElement('div');
            col.className = 'col-6 col-md-3';
            col.innerHTML = `
              <div class="border rounded-3 overflow-hidden thumb position-relative">
                <div class="ratio ratio-4x3">
                  <img src="${url}" alt="" class="img-cover">
                </div>
                <button type="button" class="btn btn-sm btn-danger remove m-1" data-idx="${idx}">
                  <i class="fa fa-times"></i>
                </button>
                <div class="px-2 py-1 small text-truncate" title="${file.name}">${file.name}</div>
              </div>
            `;
            grid.appendChild(col);
          });
        }
        function addFiles(list) {
          const incoming = Array.from(list);
          for (const f of incoming) {
            const isImage = /^image\/(png|jpeg|webp)$/i.test(f.type);
            if (!isImage) continue;
            if (f.size > MAX_SIZE) { alert(`ไฟล์ ${f.name} มีขนาดเกิน 2MB`); continue; }
            if (files.length >= MAX_FILES) { alert(`เลือกได้สูงสุด ${MAX_FILES} ไฟล์`); break; }
            files.push(f);
          }
          refreshUI();
        }
        function clearFiles() {
          files = [];
          fileInput.value = '';
          refreshUI();
        }
        grid.addEventListener('click', (e) => {
          const btn = e.target.closest('.remove');
          if (!btn) return;
          const idx = parseInt(btn.getAttribute('data-idx'), 10);
          files.splice(idx, 1);
          refreshUI();
        });
        fileInput.addEventListener('change', (e) => addFiles(e.target.files));
        ['dragenter','dragover'].forEach(ev => dz.addEventListener(ev, (e) => {
          e.preventDefault(); e.stopPropagation(); dz.classList.add('dragover');
        }));
        ['dragleave','drop'].forEach(ev => dz.addEventListener(ev, (e) => {
          e.preventDefault(); e.stopPropagation(); dz.classList.remove('dragover');
        }));
        dz.addEventListener('drop', (e) => addFiles(e.dataTransfer.files));
        btnClear?.addEventListener('click', clearFiles);

        // ส่งด้วย fetch เพื่อแนบเฉพาะไฟล์ที่ผ่านเงื่อนไข
        upForm.addEventListener('submit', (e) => {
          e.preventDefault();
          const fd = new FormData(upForm);
          fd.delete('images[]');
          files.forEach(f => fd.append('images[]', f));
          btnUpload.disabled = true;
          fetch(upForm.action, { method: 'POST', body: fd, headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} })
            .then(res => res.redirected ? (window.location.href = res.url) : res.text().then(() => window.location.reload()))
            .catch(() => { alert('อัปโหลดไม่สำเร็จ ลองใหม่อีกครั้ง'); btnUpload.disabled = false; });
        });
      }
    });
  </script>
</x-app-layout>
