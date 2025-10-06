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

              <dt class="col-sm-4 col-lg-5">สถานะ (งาน)</dt>
              <dd class="col-sm-8 col-lg-7">
                <?php $map=['รอดำเนินการ'=>'secondary','กำลังดำเนินการ'=>'warning','เสร็จสิ้น'=>'success','ยกเลิก'=>'dark']; ?>
                <span class="badge bg-<?php echo e($map[$report->status] ?? 'secondary'); ?>"><?php echo e($report->status); ?></span>
              </dd>

              <dt class="col-sm-4 col-lg-5">ผู้แจ้ง</dt>
              <dd class="col-sm-8 col-lg-7"><?php echo e($report->user?->name ?? '-'); ?></dd>

              <dt class="col-sm-4 col-lg-5">สร้างเมื่อ</dt>
              <dd class="col-sm-8 col-lg-7"><?php echo e($report->created_at?->format('Y-m-d H:i')); ?></dd>

              <?php
                $current = $report->currentAssignment;
                $latest  = $report->latestAssignment;
                $techName   = $current?->technician?->name ?? $latest?->technician?->name ?? '— ยังไม่มอบหมาย —';
                $aStatus    = $current?->status ?? $latest?->status;
                $startedAt  = $current?->started_at ?? $latest?->started_at;
                $finishedAt = $current?->finished_at ?? $latest?->finished_at;
              ?>

              <dt class="col-sm-4 col-lg-5">ผู้รับผิดชอบ</dt>
              <dd class="col-sm-8 col-lg-7"><?php echo e($techName); ?></dd>

              <?php if($aStatus): ?>
                <dt class="col-sm-4 col-lg-5">สถานะ (ผู้รับผิดชอบ)</dt>
                <dd class="col-sm-8 col-lg-7"><span class="badge bg-info"><?php echo e($aStatus); ?></span></dd>
              <?php endif; ?>

              <dt class="col-sm-4 col-lg-5">รับงาน</dt>
              <dd class="col-sm-8 col-lg-7"><?php echo e(optional($startedAt)->format('Y-m-d H:i') ?? '—'); ?></dd>

              <dt class="col-sm-4 col-lg-5">เสร็จสิ้น</dt>
              <dd class="col-sm-8 col-lg-7"><?php echo e(optional($finishedAt)->format('Y-m-d H:i') ?? '—'); ?></dd>

              <dt class="col-sm-4 col-lg-5">รายละเอียด</dt>
              <dd class="col-sm-8 col-lg-7"><?php echo e($report->detail); ?></dd>
            </dl>
          </div>

          
          <div class="col-12 col-lg-5">
            <h6 class="fw-semibold mb-2">รูปภาพประกอบ (<?php echo e($report->images?->count() ?? 0); ?>)</h6>
            <?php if($report->images?->count()): ?>
              <div class="row g-2">
                <?php $__currentLoopData = $report->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php $url = asset('storage/'.$img->path); ?>
                  <div class="col-6 col-md-4">
                    <div class="border rounded-3 overflow-hidden">
                      <a href="<?php echo e($url); ?>" target="_blank" rel="noopener">
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

      
      <div class="card-footer d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <a href="<?php echo e(route('tech.jobs.index')); ?>" class="btn btn-outline-secondary">ย้อนกลับ</a>

        <div class="d-flex flex-wrap gap-2">
          
          <?php if(!$report->currentAssignment): ?>
            <form method="POST" action="<?php echo e(route('tech.jobs.claim', $report)); ?>">
              <?php echo csrf_field(); ?>
              <button class="btn btn-primary">รับงาน</button>
            </form>
          <?php endif; ?>

          
          <?php if(optional($report->currentAssignment)->technician_id === auth()->id()): ?>
            <form method="POST" action="<?php echo e(route('tech.jobs.start', $report)); ?>">
              <?php echo csrf_field(); ?>
              <button class="btn btn-warning">เริ่มงาน</button>
            </form>
            <form method="POST" action="<?php echo e(route('tech.jobs.complete', $report)); ?>">
              <?php echo csrf_field(); ?>
              <button class="btn btn-success">ปิดงาน</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
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
<?php /**PATH D:\code\Report\resources\views\technician\jobs\show.blade.php ENDPATH**/ ?>