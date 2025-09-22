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
        <div class="card shadow" style=" width: 80%;">
            <div class="card-header text-center fw-semibold">
                ติดตามสถานะแจ้งซ่อมอุปกรณ์
            </div>

            <div class="card-body">
                <div class="container py-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">

                                <form method="GET" action="<?php echo e(route('follow')); ?>" class="d-flex" role="search">
                                    <input
                                        type="search"
                                        class="form-control me-2"
                                        name="q"
                                        value="<?php echo e($search ?? ''); ?>"
                                        placeholder="ค้นหาชื่อหรืออีเมล..."
                                    >
                                    <button class="btn btn-outline-primary" type="submit">ค้นหา</button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>ID</th>
                                            <th>ชื่อ</th>
                                            <th>ที่อยู่อุปกรณ์</th>
                                            <th>รายละเอียด</th>
                                            <th>วันที่แจ้ง</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td><?php echo e($users->firstItem() + $index); ?></td>
                                                <td><?php echo e($user->id); ?></td>
                                                <td><?php echo e($user->name); ?></td>
                                                <td>แผนก IT</td>
                                                <td>notebook เปิดไม่ติด</td>
                                                <td><?php echo e(optional($user->created_at)->format('Y-m-d H:i')); ?></td>
                                                <td>รอดำเนินการ</td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">ไม่พบข้อมูล</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end">
                                <?php echo e($users->onEachSide(1)->links()); ?>

                            </div>
                        </div>
                    </div>
                </div> <!-- /.container -->
            </div> <!-- /.card-body -->
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
<?php /**PATH D:\code\Report\resources\views/follow.blade.php ENDPATH**/ ?>