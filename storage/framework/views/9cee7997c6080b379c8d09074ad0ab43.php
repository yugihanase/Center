
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
      <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
        <span>บันทึกกิจกรรม</span>
        <form method="get" class="d-flex gap-2">
          <input type="number" name="perPage" value="<?php echo e($perPage ?? 50); ?>" class="form-control form-control-sm" style="width:100px">
          <button class="btn btn-sm btn-outline-secondary">ปรับจำนวน/หน้า</button>
        </form>
      </div>

      <div class="card-body table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>เวลา</th>
              <th>ผู้ใช้</th>
              <th>เหตุการณ์</th>
              <th>สิ่งที่กระทบ</th>
              <th>รายละเอียด</th>
              <th>IP</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td class="text-nowrap"><?php echo e($row->ts?->format('Y-m-d H:i:s')); ?></td>
                <td><?php echo e($row->user); ?></td>
                <td>
                  <span class="badge bg-secondary text-uppercase"><?php echo e($row->event); ?></span>
                  <?php if($row->source === 'assignment'): ?>
                    <span class="ms-1 badge bg-info">assignment</span>
                    <?php elseif($row->source === 'stock'): ?>
                    <span class="ms-1 badge bg-success">stock</span>
                  <?php endif; ?>
                </td>
                <td><?php echo e($row->subject); ?></td>
                <td>
                  <?php if($row->detail): ?> <?php echo e($row->detail); ?> <?php endif; ?>
                  <?php if($row->props): ?>
                    <details class="mt-1">
                      <summary class="small text-muted">properties</summary>
                      <pre class="mb-0 small"><?php echo e(json_encode($row->props, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)); ?></pre>
                    </details>
                  <?php endif; ?>
                </td>
                <td class="text-nowrap"><?php echo e($row->ip ?? '-'); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr><td colspan="6" class="text-center text-muted">— ไม่มีข้อมูล —</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
        <?php echo e($logs->onEachSide(1)->links()); ?>

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
<?php /**PATH D:\code\Report\resources\views\admin\activity_logs\index.blade.php ENDPATH**/ ?>