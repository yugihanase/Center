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
  <div class="container py-3">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-semibold">รายละเอียดงาน #<?php echo e($report->id); ?></div>
      
      <div class="card-body">
        <div class="row g-4 align-items-start">
          
          <div class="col-12 col-lg-7">
            <dl class="row mb-0">
              <dt class="col-sm-4 col-lg-5">ที่อยู่อุปกรณ์</dt>
              <dd class="col-sm-8 col-lg-7"><?php echo e($report->device_address); ?></dd>

              <dt class="col-sm-4 col-lg-5">รายการ</dt>
              <dd class="col-sm-8 col-lg-7"><?php echo e($report->device_list); ?></dd>

              <dt class="col-sm-4 col-lg-5">สถานะ</dt>
              <dd class="col-sm-8 col-lg-7">
                <?php $map=['รอดำเนินการ'=>'secondary','กำลังดำเนินการ'=>'warning','เสร็จสิ้น'=>'success','ยกเลิก'=>'dark']; ?>
                <span class="badge bg-<?php echo e($map[$report->status] ?? 'secondary'); ?>"><?php echo e($report->status); ?></span>
              </dd>

              <dt class="col-sm-4 col-lg-5">ผู้แจ้ง</dt>
              <dd class="col-sm-8 col-lg-7"><?php echo e($report->user?->name ?? '-'); ?></dd>

              <dt class="col-sm-4 col-lg-5">เมื่อ</dt>
              <dd class="col-sm-8 col-lg-7"><?php echo e($report->created_at?->format('Y-m-d H:i')); ?></dd>

              <dt class="col-sm-4 col-lg-5">รายละเอียด</dt>
              <dd class="col-sm-8 col-lg-7"><?php echo e($report->detail); ?></dd>
            </dl>
          </div>

          
          <div class="col-12 col-lg-5">
            <h6 class="fw-semibold mb-2">
              รูปภาพประกอบ (<?php echo e($report->images?->count() ?? 0); ?>)
            </h6>

            <?php if($report->images?->count()): ?>
              <div class="row g-2">
                <?php $__currentLoopData = $report->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php $url = asset('storage/'.$img->path); ?>
                  <div class="col-6 col-md-4">
                    <div class="border rounded-3 overflow-hidden">
                      <a href="<?php echo e($url); ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#imageModal"
                        data-image="<?php echo e($url); ?>"
                        data-index="<?php echo e($i); ?>">
                        <img src="<?php echo e($url); ?>" class="img-fluid" alt="<?php echo e($img->original_name); ?>">
                      </a>
                    </div>
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            <?php else: ?>
              <div class="text-muted">ไม่มีรูปแนบ</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
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
      
<div class="card-header fw-semibold">รายละเอียดการทำงาน #<?php echo e($report->id); ?></div>
<div class="card-body">
  <?php
    // เอาช่างที่เปิดอยู่ก่อน ถ้าไม่มีเปิดอยู่แล้ว fallback เป็น assignment ล่าสุด
    $current = $report->currentAssignment;
    $latest  = $report->latestAssignment;

    $techName   = $current?->technician?->name ?? $latest?->technician?->name ?? '— ยังไม่มอบหมาย —';
    $startedAt  = $current?->started_at ?? $latest?->started_at;
    $finishedAt = $current?->finished_at ?? $latest?->finished_at;
    $aStatus    = $current?->status ?? $latest?->status; // ถ้าอยากโชว์สถานะ assignment
  ?>

  <dl class="row mb-0">
    <dt class="col-sm-3">ผู้รับผิดชอบ</dt>
    <dd class="col-sm-9"><?php echo e($techName); ?></dd>

    <?php if($aStatus): ?>
      <dt class="col-sm-3">สถานะ (ผู้รับผิดชอบ)</dt>
      <dd class="col-sm-9"><span class="badge bg-info"><?php echo e($aStatus); ?></span></dd>
    <?php endif; ?>

    <dt class="col-sm-3">รับงาน</dt>
    <dd class="col-sm-9"><?php echo e(optional($startedAt)->format('Y-m-d H:i') ?? '—'); ?></dd>

    <dt class="col-sm-3">เสร็จสิ้น</dt>
    <dd class="col-sm-9"><?php echo e(optional($finishedAt)->format('Y-m-d H:i') ?? '—'); ?></dd>

    <dt class="col-sm-3">รายละเอียด</dt>
    <dd class="col-sm-9"><?php echo e($report->work_detail ?? '—'); ?></dd>
  </dl>
</div>


      <div class="card-footer d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-secondary">ย้อนกลับ</a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-reports')): ?>
          <div class="d-flex flex-wrap gap-2 align-items-center">

            
            <form method="POST"
                  action="<?php echo e(route('admin.reports.updateStatus', $report)); ?>"
                  class="d-flex align-items-center gap-2">
              <?php echo csrf_field(); ?>
              <?php echo method_field('PATCH'); ?>

              <label for="status" class="mb-0 small text-muted">เปลี่ยนสถานะ</label>

              <div class="input-group input-group-sm" style="width:auto">
                <select id="status" name="status" class="form-select form-select-sm">
                  <?php $__currentLoopData = ['รอดำเนินการ','กำลังดำเนินการ','เสร็จสิ้น','ยกเลิก']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($st); ?>" <?php if($report->status === $st): echo 'selected'; endif; ?>><?php echo e($st); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="btn btn-success">บันทึก</button>
              </div>
            </form>

            
            <form method="POST"
                  action="<?php echo e(route('admin.reports.destroy', $report)); ?>"
                  class="d-inline"
                  onsubmit="return confirm('ยืนยันการลบรายการนี้?')">
              <?php echo csrf_field(); ?>
              <?php echo method_field('DELETE'); ?>
              <button type="submit" class="btn btn-outline-danger btn-sm">ลบ</button>
            </form>

          </div>
        <?php endif; ?>
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH D:\code\Report\resources\views\admin\reports\show.blade.php ENDPATH**/ ?>