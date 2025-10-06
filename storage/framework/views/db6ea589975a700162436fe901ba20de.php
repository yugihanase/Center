
<?php
  $item = $item ?? null;
?>

<div class="mb-3">
  <label class="form-label">รหัสพนักงาน *</label>
  <input type="text" name="employee_code" value="<?php echo e(old('employee_code', $item->employee_code ?? '')); ?>"
         class="form-control" required>
</div>

<div class="mb-3">
  <label class="form-label">ชื่อ-สกุล *</label>
  <input type="text" name="name" value="<?php echo e(old('name', $item->name ?? '')); ?>" class="form-control" required>
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">บทบาท *</label>
    <select name="role" class="form-select" required>
      <?php $__currentLoopData = ['technician'=>'ช่าง','lead'=>'หัวหน้าช่าง']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($k); ?>" <?php if(old('role', $item->role ?? 'technician')===$k): echo 'selected'; endif; ?>><?php echo e($v); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label">สังกัด/ฝ่าย</label>
    <input type="text" name="department" value="<?php echo e(old('department', $item->department ?? '')); ?>" class="form-control">
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">เบอร์โทร</label>
    <input type="text" name="phone" value="<?php echo e(old('phone', $item->phone ?? '')); ?>" class="form-control">
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label">อีเมล</label>
    <input type="email" name="email" value="<?php echo e(old('email', $item->email ?? '')); ?>" class="form-control">
  </div>
</div>

<div class="mb-3">
  <label class="form-label">ผูกกับผู้ใช้ (ถ้ามี)</label>
  <select name="user_id" class="form-select">
    <option value="">— ไม่ผูก —</option>
    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <option value="<?php echo e($u->id); ?>"
        <?php if( (string)old('user_id', $item->user_id ?? '') === (string)$u->id ): echo 'selected'; endif; ?>>
        <?php echo e($u->name); ?> <?php echo e($u->email ? "({$u->email})" : ''); ?> <?php echo e($u->employee_code ? "- {$u->employee_code}" : ''); ?>

      </option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <div class="form-text">ถ้าปล่อยว่าง ระบบจะพยายามจับคู่ด้วย employee_code อัตโนมัติเมื่อบันทึก</div>
</div>

<div class="mb-3">
  <label class="form-label">หมายเหตุ</label>
  <textarea name="notes" class="form-control" rows="2"><?php echo e(old('notes', $item->notes ?? '')); ?></textarea>
</div>

<?php if($item): ?>
  <div class="form-check">
    <input class="form-check-input" type="checkbox" value="1" name="is_active" id="is_active"
           <?php if(old('is_active', $item->is_active)): echo 'checked'; endif; ?>>
    <label class="form-check-label" for="is_active">เปิดใช้งาน</label>
  </div>
<?php endif; ?>
<?php /**PATH D:\code\Report\resources\views\admin\technicians\partials\form.blade.php ENDPATH**/ ?>