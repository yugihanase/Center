<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
   <?php $__env->slot('header', null, []); ?> 
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      <?php echo e(__('ระบบแจ้งซ่อมอุปกรณ์')); ?>

    </h2>
   <?php $__env->endSlot(); ?>

  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow" style="width: 90%; max-width: 1280px;">
      <div class="card-header text-center fw-semibold">
        แจ้งปัญหา & ติดตามสถานะ
      </div>

      <div class="card-body">
        <?php if(session('success')): ?>
          <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        
        <div class="row g-4 align-items-start">

          
          <div class="col-lg-4">
            <div class="card shadow-sm h-100">
              <div class="card-header fw-semibold text-center">แจ้งปัญหา/คำขอ</div>
              <div class="card-body">
                <form method="POST" action="<?php echo e(route('report.store')); ?>" enctype="multipart/form-data">
                  <?php echo csrf_field(); ?>

                  <div class="mb-3">
                    <label for="device_address" class="form-label">สถานที่</label>
                    <input type="text" id="device_address" name="device_address"
                      class="form-control <?php $__errorArgs = ['device_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                      value="<?php echo e(old('device_address')); ?>" required>
                    <?php $__errorArgs = ['device_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                  </div>

                  <div class="mb-3">
                    <label for="device_list" class="form-label">รายการอุปกรณ์</label>
                    <input type="text" id="device_list" name="device_list"
                      class="form-control <?php $__errorArgs = ['device_list'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                      value="<?php echo e(old('device_list')); ?>" required>
                    <?php $__errorArgs = ['device_list'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                  </div>

                  <div class="mb-3">
                    <label for="detail" class="form-label">แจ้งรายละเอียด</label>
                    <textarea id="detail" name="detail" rows="3"
                      class="form-control <?php $__errorArgs = ['detail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                      required><?php echo e(old('detail')); ?></textarea>
                    <?php $__errorArgs = ['detail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                  </div>

                  
                  <div class="mb-3">
                    <label class="form-label fw-semibold">อัปโหลดรูปภาพ (หลายไฟล์)</label>

                    <div id="multiInfo" class="small text-muted mb-2">ยังไม่ได้เลือกรูป</div>

                    
                    <div id="gallery" class="row g-2 mb-2"></div>

                    <div class="d-flex gap-2 mb-1">
                      <button type="button" class="btn btn-outline-secondary" id="pickImagesBtn">
                        <i class="fas fa-images me-1"></i> เลือกรูป
                      </button>
                      <button type="button" class="btn btn-outline-danger" id="clearAllBtn">
                        ลบทั้งหมด
                      </button>
                    </div>

                    
                    <input
                      type="file"
                      id="images"
                      name="images[]"
                      class="visually-hidden <?php $__errorArgs = ['images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['images.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                      accept="image/*"
                      multiple
                    >

                    <?php $__errorArgs = ['images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>   <div class="invalid-feedback d-block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php $__errorArgs = ['images.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback d-block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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

          
          <div class="col-lg-8">
            <div class="card shadow-sm h-100">
              <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <span class="fw-semibold">ติดตามรายการ</span>

                <form method="GET" action="<?php echo e(route('report.follow')); ?>" class="d-flex gap-2 flex-grow-1" id="filterForm">
                  <select class="form-select w-auto" name="status" id="statusSelect" onchange="handleStatusChange(this)">
                    <option value="" <?php if(($status ?? '') === ''): echo 'selected'; endif; ?>>ทุกสถานะ</option>
                    <?php $__currentLoopData = ['รอดำเนินการ','กำลังดำเนินการ','เสร็จสิ้น','ยกเลิก']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <option value="<?php echo e($st); ?>" <?php if(($status ?? '') === $st): echo 'selected'; endif; ?>><?php echo e($st); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>

                  <input type="search" class="form-control" name="q" value="<?php echo e($q); ?>"
                         placeholder="ค้นหา ชื่อ/อีเมล/ที่อยู่/รายการ/รายละเอียด" id="qInput">

                  <button class="btn btn-outline-primary" type="submit">ค้นหา</button>
                </form>

                <script>
                  function handleStatusChange(selectEl) {
                    if (selectEl.value === '') {
                      window.location.href = '<?php echo e(route('report.follow')); ?>';
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
                      <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                          <td><?php echo e($reports->firstItem() + $i); ?></td>
                          <td><?php echo e($r->device_address); ?></td>
                          <td><?php echo e($r->device_list); ?></td>
                          <td class="text-truncate" style="max-width: 260px;">
                            <?php echo e(\Illuminate\Support\Str::limit($r->detail, 100)); ?>

                          </td>
                          <td>
                            <?php
                              $map = [
                                'รอดำเนินการ'   => 'secondary',
                                'กำลังดำเนินการ' => 'warning',
                                'เสร็จสิ้น'     => 'success',
                                'ยกเลิก'         => 'dark',
                              ];
                            ?>
                            <span class="badge bg-<?php echo e($map[$r->status] ?? 'secondary'); ?>"><?php echo e($r->status); ?></span>
                          </td>
                          <td><?php echo e($r->user?->name ?? '-'); ?></td>
                          <td class="text-nowrap"><?php echo e(optional($r->created_at)->format('Y-m-d H:i')); ?></td>
                          <td>
                            <a href="<?php echo e(route('report.show', $r)); ?>" class="btn btn-sm btn-outline-primary">
                              <i class="bi bi-plus-circle"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                          <td colspan="7" class="text-center text-muted">ไม่พบรายการ</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                  <?php echo e($reports->onEachSide(1)->links()); ?>

                </div>
              </div>
            </div>
          </div>
        </div> 
      </div> 
    </div>
  </div>

  
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH D:\code\Report\resources\views\report\follow.blade.php ENDPATH**/ ?>