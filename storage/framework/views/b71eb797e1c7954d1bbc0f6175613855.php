
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
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">ศูนย์จ่ายงาน</h2>
   <?php $__env->endSlot(); ?>

  <div class="container-fluid py-3">
    <div class="row g-3">

      
      <div class="col-lg-7">
        <div class="card shadow-sm">
          <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
            <span>คิวเข้ามา (ยังไม่มอบหมาย)</span>
            <small class="text-muted">แสดงเฉพาะงานที่ยังไม่ปิด</small>
          </div>

          <div class="card-body">
            <form id="bulkForm" class="mb-2">
              <?php echo csrf_field(); ?>
              <div class="d-flex flex-wrap gap-2">
                <select id="bulkTech" class="form-select form-select-sm" style="max-width:260px">
                  <option value="">— เลือกช่าง —</option>
                  <?php $__currentLoopData = $techs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($t->id); ?>"><?php echo e($t->name); ?> (<?php echo e($t->open_jobs_count); ?> งาน)</option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="button" class="btn btn-primary btn-sm" onclick="bulkAssign()">
                  มอบหมายที่เลือก
                </button>
              </div>
            </form>

            <?php $CLOSED = ['เสร็จสิ้น','ยกเลิก']; ?>

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
                  <?php $__empty_1 = true; $__currentLoopData = $unassigned; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if(in_array($r->status ?? '', $CLOSED, true)) continue; ?>
                    <tr>
                      <td><input type="checkbox" class="rowChk" value="<?php echo e($r->id); ?>"></td>
                      <td><?php echo e($unassigned->firstItem() + $i); ?></td>
                      <td class="text-truncate" style="max-width:220px"><?php echo e($r->device_address); ?></td>
                      <td class="text-truncate" style="max-width:180px"><?php echo e($r->device_list); ?></td>
                      <td><?php echo e($r->requester_name ?? ($r->user?->name ?? '-')); ?></td>
                      <td><?php echo e($r->created_at->format('Y-m-d H:i')); ?></td>
                      <td>
                        <div class="input-group input-group-sm">
                          <select class="form-select form-select-sm" id="tech-<?php echo e($r->id); ?>">
                            <option value="">เลือกช่าง…</option>
                            <?php $__currentLoopData = $techs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($t->id); ?>"><?php echo e($t->name); ?> (<?php echo e($t->open_jobs_count); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                          </select>
                          
                          <button type="button" class="btn btn-outline-primary" onclick="assign(<?php echo e($r->id); ?>, this)">
                            มอบหมาย
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="text-center text-muted">ไม่มีงานรอมอบหมาย</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <div class="pt-2">
              <?php echo e($unassigned->withQueryString()->links()); ?>

            </div>
          </div>
        </div>
      </div>

      
      <div class="col-lg-5">

        
        <div class="card shadow-sm mb-3">
          <div class="card-header fw-semibold">ช่างทั้งหมด</div>
          <div class="card-body p-0">
            <ul class="list-group list-group-flush">
              <?php $__currentLoopData = $techs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $active = ($viewTechId ?? 0) === $t->id;
                  // ใช้ fullUrlWithQuery เพื่อสร้างลิงก์ ?view_tech=...
                  $url = request()->fullUrlWithQuery([
                    'view_tech' => $t->id,
                    'page'      => null,     // รีเซ็ตเพจฝั่งซ้าย
                    'tech_page' => null,     // รีเซ็ตเพจฝั่งขวา
                  ]);
                ?>

                <li class="list-group-item d-flex justify-content-between align-items-center <?php if($active): ?> bg-light <?php endif; ?>" style="position:relative">
                  <a href="<?php echo e($url); ?>" class="stretched-link text-decoration-none <?php if($active): ?> fw-semibold text-primary <?php else: ?> text-body <?php endif; ?>">
                    <div><?php echo e($t->name); ?></div>
                    <small class="text-muted"><?php echo e($t->email); ?></small>
                  </a>
                  <span class="badge <?php if($active): ?> bg-primary <?php else: ?> bg-secondary <?php endif; ?> rounded-pill">
                    <?php echo e($t->open_jobs_count); ?> งานเปิด
                  </span>
                </li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
          </div>
        </div>

        
        <div class="card shadow-sm">
          <div class="card-header fw-semibold">
            <?php if(!empty($selectedTech)): ?>
              งานที่เปิดอยู่ของ: <span class="text-primary"><?php echo e($selectedTech->name); ?></span>
            <?php else: ?>
              คลิกรายชื่อช่างด้านบนเพื่อดูงานที่เปิดอยู่
            <?php endif; ?>
          </div>

          <?php if(!empty($selectedTech)): ?>
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
                    <?php $__empty_1 = true; $__currentLoopData = $techOpen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                      <?php
                        $r = $a->report;
                        $st = $a->status;
                        $badge = $st === 'กำลังดำเนินการ' ? 'warning' : 'secondary';
                      ?>
                      <tr>
                        <td><?php echo e($techOpen->firstItem() + $idx); ?></td>
                        <td class="text-truncate" style="max-width:200px"><?php echo e($r?->device_address ?? '-'); ?></td>
                        <td class="text-truncate" style="max-width:180px"><?php echo e($r?->device_list ?? '-'); ?></td>
                        <td><span class="badge bg-<?php echo e($badge); ?>"><?php echo e($st); ?></span></td>
                        <td class="text-end">
                          <a href="<?php echo e(route('tech.jobs.show', $r?->id)); ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-zoom-in"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                      <tr><td colspan="5" class="text-center text-muted">ไม่มีงานเปิดอยู่</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              
              <div class="p-2">
                <?php echo e($techOpen->appends(request()->except('tech_page') + ['view_tech' => $viewTechId])->links()); ?>

              </div>
            </div>
          <?php endif; ?>
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
          'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
        await postJson(`<?php echo e(route('admin.jobs.assign.store')); ?>`, {
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
        await postJson(`<?php echo e(route('admin.jobs.bulk')); ?>`, {
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
<?php /**PATH D:\code\Report\resources\views\admin\jobs\assign.blade.php ENDPATH**/ ?>