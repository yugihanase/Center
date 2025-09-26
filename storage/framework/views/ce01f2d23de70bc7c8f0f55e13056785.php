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
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-0">
        <?php echo e(__('ระบบจัดการคลังวัสดุ')); ?>

        </h2>

        <div class="d-flex flex-wrap align-items-center gap-2">

        
        <button class="btn btn-success btn-sm"
                data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa fa-plus-circle me-1"></i> เพิ่ม
        </button>

        
        <form method="POST"
                action="<?php echo e(route('admin.stock.import')); ?>"
                enctype="multipart/form-data"
                class="d-flex align-items-center gap-2">
            <?php echo csrf_field(); ?>

            <div class="input-group input-group-sm">
            <input type="file"
                    name="csv_file"
                    accept=".csv,text/csv"
                    class="form-control"
                    aria-label="เลือกไฟล์ CSV"
                    required>
            <button class="btn btn-outline-primary" type="submit" disabled id="btnImport">
                <i class="fa fa-download me-1"></i> Import
            </button>
            </div>
        </form>

        
        <a class="btn btn-outline-secondary btn-sm"
            href="<?php echo e(route('admin.stock.export')); ?>">
            <i class="fa fa-upload me-1"></i> Export
        </a>

        <a class="btn btn-outline-dark btn-sm"
            href="<?php echo e(route('admin.stock.template')); ?>">
            <i class="fa fa-file-csv me-1"></i> ฟอร์มตัวอย่าง CSV
        </a>
        </div>
    </div>

    
    <script>
        (function () {
        const form = document.currentScript.closest('x-slot')?.parentElement || document;
        const fileInput = form.querySelector('input[name="csv_file"]');
        const btnImport = form.querySelector('#btnImport');
        if (fileInput && btnImport) {
            fileInput.addEventListener('change', () => {
            btnImport.disabled = !fileInput.files.length;
            }, { passive: true });
        }
        })();
    </script>
     <?php $__env->endSlot(); ?>

  <?php
    $q = request('q');
  ?>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

        <!-- แถบค้นหา/ตัวกรอง -->
        <div class="p-4 border-bottom">
          <form method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
              <input type="search" name="q" value="<?php echo e($q ?? ''); ?>" class="form-control"
                     placeholder="ค้นหา รายการ/หมวดหมู่">
            </div>
            <div class="col-6 col-md-3">
              
              
            </div>
            <div class="col-6 col-md-3 d-grid d-md-block">
              <button class="btn btn-primary w-100 w-md-auto">
                <i class="fa fa-search me-1"></i> ค้นหา
              </button>
            </div>
          </form>
        </div>

        <div class="p-4">
          <!-- Alert -->
          <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
          <?php endif; ?>
          <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
          <?php endif; ?>

            <!-- ตาราง -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 65vh;">
                        <?php
                        $sort = request('sort','name');
                        $dir  = strtolower(request('dir','asc')) === 'desc' ? 'desc' : 'asc';

                        // คลาสสไตล์ DataTables: sorting / sorting_asc / sorting_desc
                        $sortClass = function(string $field) use ($sort,$dir) {
                            if ($sort !== $field) return 'sorting';
                            return $dir === 'asc' ? 'sorting_asc' : 'sorting_desc';
                        };

                        // ลิงก์สลับทิศ (ถ้ากดคอลัมน์เดิมจะสลับ asc<->desc), และ reset page=1
                        $sortUrl = function(string $field) use ($sort,$dir) {
                            $next = ($sort === $field && $dir === 'asc') ? 'desc' : 'asc';
                            return request()->fullUrlWithQuery(['sort'=>$field,'dir'=>$next,'page'=>1]);
                        };
                        ?>

                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light text-center sticky-top" style="position: sticky; top: 0; z-index: 1;">
  <tr>
    <th style="width:72px;">#</th>

    <th class="<?php echo e($sortClass('name')); ?> text-start">
      <a href="<?php echo e($sortUrl('name')); ?>">รายการ</a>
    </th>

    <th class="<?php echo e($sortClass('book_qty')); ?> text-end">
      <a class="d-block text-end" href="<?php echo e($sortUrl('book_qty')); ?>">ยอดคงคลัง</a>
    </th>

    <th class="<?php echo e($sortClass('remain')); ?> text-end">
      <a class="d-block text-end" href="<?php echo e($sortUrl('remain')); ?>">คงเหลือ</a>
    </th>

    <th class="<?php echo e($sortClass('unit')); ?>">
      <a href="<?php echo e($sortUrl('unit')); ?>">หน่วยนับ</a>
    </th>

    <th class="<?php echo e($sortClass('used_qty')); ?> text-end">
      <a class="d-block text-end" href="<?php echo e($sortUrl('used_qty')); ?>">จำนวนที่ใช้ไป</a>
    </th>

    <th class="<?php echo e($sortClass('category')); ?> text-start">
      <a href="<?php echo e($sortUrl('category')); ?>">หมวดหมู่</a>
    </th>

    <th style="width:160px;">จัดการ</th>
  </tr>
</thead>

                        <tbody>
                        <?php
                            $offset = (is_object($stocks) && method_exists($stocks, 'firstItem') && !is_null($stocks->firstItem()))
                            ? ((int)$stocks->firstItem() - 1)
                            : 0;
                        ?>

                        <?php if($stocks && (method_exists($stocks,'count') ? $stocks->count() : count($stocks))): ?>
                            <?php $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-center"><?php echo e($offset + $index + 1); ?></td>
                                <td class="fw-semibold"><?php echo e($s->name); ?></td>

                                <td class="text-end"><?php echo e(number_format($s->book_qty)); ?></td>
                                <td class="text-end <?php echo e($s->remain <= 0 ? 'text-danger fw-semibold' : ''); ?>"><?php echo e(number_format($s->remain)); ?></td>

                                <td class="text-center"><?php echo e($s->unit); ?></td>
                                <td class="text-end"><?php echo e(number_format($s->used_qty_int)); ?></td>
                                <td><?php echo e($s->category?->name ?? '-'); ?></td>

                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">

                                        
                                        <button class="btn btn-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#inModal<?php echo e($s->id); ?>">
                                        เติม
                                        </button>

                                        
                                        <button class="btn btn-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#outModal<?php echo e($s->id); ?>"
                                                <?php if(($s->remain ?? 0) <= 0): echo 'disabled'; endif; ?>>
                                        เบิก
                                        </button>

                                        
                                        <button class="btn btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#delModal<?php echo e($s->id); ?>">
                                        ลบ
                                        </button>
                                    </div>

                                    
                                    <div class="modal fade" id="inModal<?php echo e($s->id); ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                        <form class="modal-content" method="POST" action="<?php echo e(route('admin.stock.in', $s)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <div class="modal-header">
                                            <h5 class="modal-title">เติมสต็อก: <?php echo e($s->name); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                            <div class="mb-2">
                                                <label class="form-label">จำนวน (<?php echo e($s->unit); ?>)</label>
                                                <input type="number" name="qty" min="1" class="form-control" required>
                                            </div>
                                            <div>
                                                <label class="form-label">หมายเหตุ</label>
                                                <input type="text" name="note" class="form-control" placeholder="เช่น ใบสั่งซื้อ #PO123">
                                            </div>
                                            </div>
                                            <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                            <button class="btn btn-success">บันทึก</button>
                                            </div>
                                        </form>
                                        </div>
                                    </div>

                                    
                                    <div class="modal fade" id="outModal<?php echo e($s->id); ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                        <form class="modal-content" method="POST" action="<?php echo e(route('admin.stock.out', $s)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <div class="modal-header">
                                            <h5 class="modal-title">เบิก: <?php echo e($s->name); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                            <?php
                                                // ใช้อะไรที่คุณมี: ถ้าใช้โมเดล accessor ให้เรียก $s->remain ได้เลย
                                                $remain = (int)($s->remain ?? 0);
                                            ?>
                                            <div class="mb-2">
                                                <label class="form-label">จำนวน (คงเหลือ <?php echo e(number_format($remain)); ?> <?php echo e($s->unit); ?>)</label>
                                                <input type="number" name="qty" min="1" max="<?php echo e($remain); ?>" class="form-control" required>
                                            </div>
                                            <div>
                                                <label class="form-label">หมายเหตุ</label>
                                                <input type="text" name="note" class="form-control" placeholder="เช่น งาน PM ฝ่ายช่าง">
                                            </div>
                                            </div>
                                            <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                            <button class="btn btn-warning" <?php if($remain <= 0): echo 'disabled'; endif; ?>>บันทึก</button>
                                            </div>
                                        </form>
                                        </div>
                                    </div>

                                    
                                    <div class="modal fade" id="delModal<?php echo e($s->id); ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                        <form class="modal-content" method="POST" action="<?php echo e(route('admin.stock.destroy', $s)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <div class="modal-header">
                                            <h5 class="modal-title">ลบรายการ</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                            ยืนยันลบ: <strong><?php echo e($s->name); ?></strong>
                                            <div class="small text-muted mt-2">
                                                ระบบจะลบประวัติรับเข้า/เบิกออกของรายการนี้ทั้งหมดด้วย
                                            </div>
                                            </div>
                                            <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                            <button class="btn btn-outline-danger">ลบ</button>
                                            </div>
                                        </form>
                                        </div>
                                    </div>
                                </td>

                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <tr>
                            <td colspan="8" class="text-center text-muted py-5">ยังไม่มีข้อมูลสต็อก</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    </div>

                    
                    <?php if(is_object($stocks) && method_exists($stocks, 'links')): ?>
                    <div class="p-3">
                        <?php echo e($stocks->appends(request()->query())->links()); ?>

                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
      </div>
    </div>
  </div>

  
  <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" method="POST" action="<?php echo e(route('admin.stock.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h5 class="modal-title">เพิ่มรายการสต็อก</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">รายการ</label>
            <input name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required autofocus>
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="mb-2">
            <label class="form-label">หมวดหมู่</label>
            <input name="category_name" class="form-control <?php $__errorArgs = ['category_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   placeholder="เช่น วัสดุสิ้นเปลือง" required>
            <?php $__errorArgs = ['category_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="mb-2">
            <label class="form-label">หน่วยนับ</label>
            <input name="unit" class="form-control <?php $__errorArgs = ['unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   placeholder="กล่อง / ชิ้น / ม้วน" required>
            <?php $__errorArgs = ['unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div>
            <label class="form-label">ยอดตั้งต้น</label>
            <input type="number" name="qty_open" class="form-control <?php $__errorArgs = ['qty_open'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" min="0" value="0">
            <?php $__errorArgs = ['qty_open'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button class="btn btn-success">บันทึก</button>
        </div>
      </form>
    </div>
  </div>

  
  <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" method="POST" action="<?php echo e(route('admin.stock.import')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h5 class="modal-title">นำเข้า CSV</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-2">
            รูปแบบหัวคอลัมน์ที่รองรับ:
            <code>name, category, unit, qty_open, in_qty, out_qty, note</code>
          </p>
          <input type="file" name="csv_file" accept=".csv" class="form-control" required>
        </div>
        <div class="modal-footer">
          <a href="<?php echo e(route('admin.stock.export')); ?>" class="btn btn-link">ดาวน์โหลดตัวอย่าง (Export)</a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button class="btn btn-primary">นำเข้า</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal fade" id="inModalShared" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="POST" id="inForm" action="#">
      <?php echo csrf_field(); ?>
      <div class="modal-header">
        <h5 class="modal-title" id="inTitle">เติมสต็อก</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label" id="inLabel">จำนวน</label>
          <input type="number" name="qty" min="1" class="form-control" required>
        </div>
        <div>
          <label class="form-label">หมายเหตุ</label>
          <input type="text" name="note" class="form-control" placeholder="เช่น ใบสั่งซื้อ #PO123">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button class="btn btn-success">บันทึก</button>
      </div>
    </form>
  </div>
</div>


  
    <script>
        document.addEventListener('shown.bs.modal', (e) => {
            const first = e.target.querySelector('input, select, textarea');
            if (first) first.focus();
        }, { passive: true });
    </script>

    <script>
        document.addEventListener('shown.bs.modal', (e) => {
            const first = e.target.querySelector('input, select, textarea, button:not([type="button"])');
            if (first) first.focus();
        }, { passive: true });
    </script>
    <script>
        document.addEventListener('show.bs.modal', function (e) {
            if (e.target.id !== 'inModalShared') return;
            const btn = e.relatedTarget;
            const form = document.getElementById('inForm');
            const title = document.getElementById('inTitle');
            const label = document.getElementById('inLabel');

            form.action = btn.getAttribute('data-action');
            const name  = btn.getAttribute('data-name') || '';
            const unit  = btn.getAttribute('data-unit') || '';

            title.textContent = `เติมสต็อก: ${name}`;
            label.textContent = `จำนวน (${unit})`;

            // โฟกัสช่องจำนวน
            setTimeout(() => {
            form.querySelector('input[name="qty"]')?.focus();
            }, 150);
        }, { passive: true });
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
<?php /**PATH D:\code\Report\resources\views/admin/stock.blade.php ENDPATH**/ ?>