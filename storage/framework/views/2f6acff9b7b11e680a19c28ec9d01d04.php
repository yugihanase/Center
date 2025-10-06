
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
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">คิวงานของช่าง</h2>
   <?php $__env->endSlot(); ?>

  <?php
    $tab    = $tab    ?? request('tab','my-queue');   // my-queue | unassigned | history
    $q      = $q      ?? request('q','');
    $status = $status ?? request('status','');

    $statusMap = [
      'รอดำเนินการ'   => 'secondary',
      'กำลังดำเนินการ' => 'warning',
      'เสร็จสิ้น'     => 'success',
      'ยกเลิก'        => 'dark',
    ];

    $CLOSED = ['เสร็จสิ้น','ยกเลิก'];   // NEW: สถานะที่ให้ซ่อนออกจาก my-queue / unassigned
  ?>

  <div class="py-4 container">
    <?php if(session('success')): ?>
      <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
      <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    
    <ul class="nav nav-pills mb-3">
      <li class="nav-item">
        <a class="nav-link <?php if($tab==='my-queue'): ?> active <?php endif; ?>"
           href="<?php echo e(route('tech.jobs.index',['tab'=>'my-queue'] + request()->except('page'))); ?>">
          งานของฉัน (กำลังทำ/รอทำ)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php if($tab==='unassigned'): ?> active <?php endif; ?>"
           href="<?php echo e(route('tech.jobs.index',['tab'=>'unassigned'] + request()->except('page'))); ?>">
          คิวที่ยังไม่ถูกมอบหมาย
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php if($tab==='history'): ?> active <?php endif; ?>"
           href="<?php echo e(route('tech.jobs.index',['tab'=>'history'] + request()->except('page'))); ?>">
          ประวัติของฉัน
        </a>
      </li>
    </ul>

    
    <form method="GET" action="<?php echo e(route('tech.jobs.index')); ?>" class="row g-2 align-items-end mb-3">
      <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
      <div class="col-md-5">
        <label class="form-label">ค้นหา</label>
        <input type="search" name="q" value="<?php echo e($q); ?>" class="form-control"
               placeholder="ที่อยู่อุปกรณ์ / รายการ / รายละเอียด">
      </div>
      <div class="col-md-3">
        <label class="form-label">สถานะ</label>
        <select name="status" class="form-select">
          <option value="">ทุกสถานะ</option>
          <?php $__currentLoopData = ['รอดำเนินการ','กำลังดำเนินการ','เสร็จสิ้น','ยกเลิก']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($st); ?>" <?php if($status===$st): echo 'selected'; endif; ?>><?php echo e($st); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary w-100">กรองข้อมูล</button>
      </div>
      <div class="col-md-2">
        <a class="btn btn-outline-secondary w-100"
           href="<?php echo e(route('tech.jobs.index',['tab'=>$tab])); ?>">ล้างค่า</a>
      </div>
    </form>

    
    <div class="card shadow-sm">
      <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th style="width:72px">#</th>
              <th>ที่อยู่อุปกรณ์</th>
              <th>รายการ</th>
              <th style="width:140px">สถานะ</th>
              <th class="text-end" style="width:220px">รายละเอียด</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <?php
                // ---- คำนวณค่าใช้แสดงผล ----
                $pmInt   = [1=>'danger', 2=>'warning', 3=>'secondary', 4=>'secondary', 5=>'success'];
                $labelInt= [1=>'สูงสุด', 2=>'สูง', 3=>'ปกติ', 4=>'ต่ำ', 5=>'ต่ำมาก'];
                $pmStr   = ['urgent'=>'danger','high'=>'warning','normal'=>'secondary','low'=>'success'];

                $prio  = $r->priority ?? null;
                $badge = is_numeric($prio) ? ($pmInt[(int)$prio] ?? 'secondary')
                                           : ($pmStr[strtolower((string)$prio)] ?? 'secondary');
                $label = is_numeric($prio) ? ($labelInt[(int)$prio] ?? $prio)
                                           : ( $prio ? ucfirst((string)$prio) : '-' );

                $due = $r->due_at instanceof \Illuminate\Support\Carbon ? $r->due_at
                      : (\Illuminate\Support\Str::of((string)($r->due_at ?? ''))->isNotEmpty()
                          ? \Illuminate\Support\Carbon::parse($r->due_at) : null);

                $stText = $r->status ?? 'รอดำเนินการ';
                $stBadge= $statusMap[$stText] ?? 'secondary';
              ?>

              
              <?php if($tab !== 'history' && in_array($stText, $CLOSED, true)): ?>
                <?php continue; ?>
              <?php endif; ?>
              

              <tr>
                <td><?php echo e($reports->firstItem() + $i); ?></td>
                <td><?php echo e($r->device_address); ?></td>
                <td><?php echo e($r->device_list); ?></td>
                <td><span class="badge bg-<?php echo e($stBadge); ?>"><?php echo e($stText); ?></span></td>
                <td class="text-end">
                  <a href="<?php echo e(route('tech.jobs.show', $r->id)); ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-zoom-in"></i>
                  </a>

                  <?php if($tab === 'unassigned'): ?>
                    <form method="POST" action="<?php echo e(route('tech.jobs.claim', $r)); ?>" class="d-inline">
                      <?php echo csrf_field(); ?>
                      <button class="btn btn-sm btn-outline-primary"
                              onclick="return confirm('ยืนยันรับงานนี้?')">
                        รับงาน
                      </button>
                    </form>
                  <?php else: ?>
                    <?php if(in_array($stText, ['รอดำเนินการ','กำลังดำเนินการ'], true)): ?>
                      <form method="POST" action="<?php echo e(route('tech.jobs.start', $r)); ?>" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-sm btn-outline-warning">เริ่มงาน</button>
                      </form>
                      <form method="POST" action="<?php echo e(route('tech.jobs.complete', $r)); ?>" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-sm btn-success">เสร็จงาน</button>
                      </form>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="7" class="text-center text-muted">ไม่พบข้อมูล</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

        <?php echo e($reports->withQueryString()->links()); ?>

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
<?php /**PATH D:\code\Report\resources\views\technician\jobs\index.blade.php ENDPATH**/ ?>