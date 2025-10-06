
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
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">จัดการรายชื่อช่าง</h2>
   <?php $__env->endSlot(); ?>

  <div class="container py-3">
    <?php if(session('success')): ?>
      <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <form method="GET" class="row g-2">
          <div class="col-md-4">
            <input type="search" name="q" value="<?php echo e($q); ?>" class="form-control" placeholder="ค้นหา รหัส/ชื่อ/เบอร์/อีเมล/ฝ่าย">
          </div>
          <div class="col-md-3">
            <select name="role" class="form-select">
              <option value="">ทุกบทบาท</option>
              <option value="technician" <?php if($role==='technician'): echo 'selected'; endif; ?>>ช่าง</option>
              <option value="lead" <?php if($role==='lead'): echo 'selected'; endif; ?>>หัวหน้าช่าง</option>
            </select>
          </div>
          <div class="col-md-3">
            <select name="active" class="form-select">
              <option value="">ทั้งหมด (เปิด/ปิด)</option>
              <option value="1" <?php if($active==='1'): echo 'selected'; endif; ?>>เฉพาะเปิดใช้งาน</option>
              <option value="0" <?php if($active==='0'): echo 'selected'; endif; ?>>เฉพาะปิดใช้งาน</option>
            </select>
          </div>
          <div class="col-md-2 d-grid">
            <button class="btn btn-primary">ค้นหา</button>
          </div>
        </form>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">รายชื่อช่าง (<?php echo e($techs->total()); ?>)</h5>
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa fa-plus-circle me-1"></i> เพิ่มจากรหัสพนักงาน
      </button>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>รหัสพนักงาน</th>
              <th>ชื่อ</th>
              <th>บทบาท</th>
              <th>เบอร์</th>
              <th>อีเมล</th>
              <th>ฝ่าย</th>
              <th>สถานะ</th>
              <th class="text-end">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $techs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($t->employee_code); ?></td>
                <td><?php echo e($t->name); ?></td>
                <td>
                  <?php if($t->role === 'lead'): ?>
                    <span class="badge bg-dark">หัวหน้าช่าง</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">ช่าง</span>
                  <?php endif; ?>
                </td>
                <td><?php echo e($t->phone); ?></td>
                <td><?php echo e($t->email); ?></td>
                <td><?php echo e($t->department); ?></td>
                <td>
                  <span class="badge <?php echo e($t->is_active ? 'bg-success' : 'bg-danger'); ?>">
                    <?php echo e($t->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน'); ?>

                  </span>
                </td>
                <td class="text-end">
                  
                  <?php if($t->role === 'lead'): ?>
                    <form method="POST" action="<?php echo e(route('admin.technicians.demote', $t)); ?>" class="d-inline">
                      <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                      <button class="btn btn-outline-warning btn-sm">ลดเป็นช่าง</button>
                    </form>
                  <?php else: ?>
                    <form method="POST" action="<?php echo e(route('admin.technicians.promote', $t)); ?>" class="d-inline">
                      <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                      <button class="btn btn-outline-dark btn-sm">ตั้งเป็นหัวหน้า</button>
                    </form>
                  <?php endif; ?>

                  
                  <form method="POST" action="<?php echo e(route('admin.technicians.toggle', $t)); ?>" class="d-inline">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <button class="btn btn-outline-secondary btn-sm">
                      <?php echo e($t->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน'); ?>

                    </button>
                  </form>

                  
                  <button class="btn btn-primary btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#editModal<?php echo e($t->id); ?>">แก้ไข</button>

                  
                  <form method="POST" action="<?php echo e(route('admin.technicians.destroy', $t)); ?>" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="btn btn-outline-danger btn-sm">ลบ</button>
                  </form>
                </td>
              </tr>

              
              <div class="modal fade" id="editModal<?php echo e($t->id); ?>" tabindex="-1">
                <div class="modal-dialog">
                  <form class="modal-content" method="POST" action="<?php echo e(route('admin.technicians.update', $t)); ?>">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <div class="modal-header">
                      <h5 class="modal-title">แก้ไขช่าง: <?php echo e($t->name); ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <?php echo $__env->make('admin.technicians.partials.form', ['item' => $t], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    <div class="modal-footer">
                      <button class="btn btn-primary">บันทึก</button>
                    </div>
                  </form>
                </div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr><td colspan="8" class="text-center text-muted">— ไม่มีข้อมูล —</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <?php echo e($techs->onEachSide(1)->links()); ?>

      </div>
    </div>
  </div>

  
  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="POST" action="<?php echo e(route('admin.technicians.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h5 class="modal-title">เพิ่มรายชื่อช่างจากรหัสพนักงาน</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php echo $__env->make('admin.technicians.partials.form', ['item' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success">เพิ่ม</button>
        </div>
      </form>
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
<?php /**PATH D:\code\Report\resources\views\admin\technicians\index.blade.php ENDPATH**/ ?>