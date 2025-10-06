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
      <?php echo e(__('ระบบจัดการงานช่าง')); ?>

    </h2>
   <?php $__env->endSlot(); ?>

  <div class="container-fluid py-3">
    
    <div class="row g-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="fw-semibold">จำนวนงานซ่อมทั้งหมด</div>
                <div class="display-6"><?php echo e($job_total); ?></div>
              </div>
              <i class="fas fa-tools fa-2x text-secondary"></i>
            </div>
            <div class="mt-2 small text-muted-white">
              รอดำเนินการ: <?php echo e($job_wait); ?> | กำลังดำเนินการ: <?php echo e($job_doing); ?> | เสร็จสิ้น: <?php echo e($job_done); ?>

            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-secondary text-white">
          <div class="card-body">
            <div class="fw-semibold mb-1">รอดำเนินการ</div>
            <div class="display-6"><?php echo e($job_wait); ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-white">
          <div class="card-body">
            <div class="fw-semibold mb-1">กำลังดำเนินการ</div>
            <div class="display-6"><?php echo e($job_doing); ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
          <div class="card-body">
            <div class="fw-semibold mb-1">ดำเนินการเสร็จแล้ว</div>
            <div class="display-6"><?php echo e($job_done); ?></div>
          </div>
        </div>
      </div>
    </div>

    
    <div class="row g-3 mt-1">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-semibold">จำนวนคน (ช่าง)</div>
              <i class="fas fa-user-cog"></i>
            </div>
            <div class="row text-center">
              <div class="col">
                <div class="small text-muted">รวม</div>
                <div class="h3"><?php echo e($tech_total); ?></div>
              </div>
              <div class="col">
                <div class="small text-muted">กำลังทำงาน</div>
                <div class="h3"><?php echo e($tech_busy); ?></div>
              </div>
              <div class="col">
                <div class="small text-muted">ว่าง</div>
                <div class="h3"><?php echo e($tech_idle); ?></div>
              </div>
            </div>
            <canvas id="techChart" style="width:100%; max-width:520px; height:220px;"></canvas>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-semibold">จำนวนรถ</div>
              <i class="fas fa-truck"></i>
            </div>
            <div class="row text-center">
              <div class="col">
                <div class="small text-muted">รวม</div>
                <div class="h3"><?php echo e($veh_total); ?></div>
              </div>
              <div class="col">
                <div class="small text-muted">กำลังใช้งาน</div>
                <div class="h3"><?php echo e($veh_inuse); ?></div>
              </div>
              <div class="col">
                <div class="small text-muted">ว่าง</div>
                <div class="h3"><?php echo e($veh_free); ?></div>
              </div>
            </div>
            <canvas id="vehChart" height="120" class="mt-3"></canvas>
          </div>
        </div>
      </div>
    </div>

    
    <div class="row g-3 mt-1">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-header fw-semibold">ตารางสรุปงานล่าสุด</div>
          <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>ที่อยู่อุปกรณ์</th>
                  <th>รายการ</th>
                  <th>สถานะ</th>
                  <th>ผู้แจ้ง</th>
                  <th>เมื่อ</th>
                  <th>จัดการ</th>
                </tr>
              </thead>
              <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $latestReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <tr>
                    <td><?php echo e($i+1); ?></td>
                    <td><?php echo e($r->device_address); ?></td>
                    <td><?php echo e($r->device_list); ?></td>
                    <td>
                      <?php
                        $map=['รอดำเนินการ'=>'secondary','กำลังดำเนินการ'=>'warning','เสร็จสิ้น'=>'success','ยกเลิก'=>'dark'];
                      ?>
                      <span class="badge bg-<?php echo e($map[$r->status] ?? 'secondary'); ?>"><?php echo e($r->status); ?></span>
                    </td>
                    <td><?php echo e($r->user?->name ?? '-'); ?></td>
                    <td><?php echo e($r->created_at?->format('Y-m-d H:i')); ?></td>
                    <td>
                        <a href="<?php echo e(route('admin.reports.show', $r)); ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-zoom-in"></i>
                        </a>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr><td colspan="6" class="text-center text-muted">ยังไม่มีข้อมูล</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header fw-semibold">ระบบ/เมนูด่วน</div>
          <div class="card-body d-grid gap-2">
            <a class="btn btn-outline-primary" href="<?php echo e(route('admin.jobs.assign')); ?>">
              ระบบจ่ายงานช่าง (เตรียมพื้นที่)
            </a>
            <a class="btn btn-outline-secondary" href="<?php echo e(route('admin.borrow.staff')); ?>">
              แจ้งยืมคน (เตรียมพื้นที่)
            </a>
            <a class="btn btn-outline-dark" href="<?php echo e(route('admin.borrow.vehicle')); ?>">
              แจ้งยืมรถ (เตรียมพื้นที่)
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // คน (ช่าง)
    new Chart(document.getElementById('techChart'), {
      type: 'doughnut',
      data: {
        labels: ['กำลังทำงาน','ว่าง'],
        datasets: [{ data: [<?php echo e($tech_busy); ?>, <?php echo e($tech_idle); ?>] }]
      }
    });

    // รถ
    new Chart(document.getElementById('vehChart'), {
      type: 'doughnut',
      data: {
        labels: ['กำลังใช้งาน','ว่าง'],
        datasets: [{ data: [<?php echo e($veh_inuse); ?>, <?php echo e($veh_free); ?>] }]
      }
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
<?php endif; ?>
<?php /**PATH D:\code\Report\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>