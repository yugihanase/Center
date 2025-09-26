<x-app-layout>
    <x-slot name="header">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-0">
        {{ __('ระบบจัดการคลังวัสดุ') }}
        </h2>

        <div class="d-flex flex-wrap align-items-center gap-2">

        {{-- เพิ่มรายการ --}}
        <button class="btn btn-success btn-sm"
                data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa fa-plus-circle me-1"></i> เพิ่ม
        </button>

        {{-- Import CSV (ฟอร์มเดียวพอ) --}}
        <form method="POST"
                action="{{ route('admin.stock.import') }}"
                enctype="multipart/form-data"
                class="d-flex align-items-center gap-2">
            @csrf

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

        {{-- Export / Template --}}
        <a class="btn btn-outline-secondary btn-sm"
            href="{{ route('admin.stock.export') }}">
            <i class="fa fa-upload me-1"></i> Export
        </a>

        <a class="btn btn-outline-dark btn-sm"
            href="{{ route('admin.stock.template') }}">
            <i class="fa fa-file-csv me-1"></i> ฟอร์มตัวอย่าง CSV
        </a>
        </div>
    </div>

    {{-- UX: เปิดปุ่ม Import เมื่อเลือกไฟล์แล้ว --}}
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
    </x-slot>

  @php
    $q = request('q');
  @endphp

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

        <!-- แถบค้นหา/ตัวกรอง -->
        <div class="p-4 border-bottom">
          <form method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
              <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control"
                     placeholder="ค้นหา รายการ/หมวดหมู่">
            </div>
            <div class="col-6 col-md-3">
              {{-- ถ้ามีดรอปดาวน์หมวดหมู่ให้ใส่ที่นี่ --}}
              {{-- <select name="category_id" class="form-select">
                   <option value="">ทุกหมวดหมู่</option>
                   @foreach($categories as $c)
                     <option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>
                   @endforeach
                 </select> --}}
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
          @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif

            <!-- ตาราง -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 65vh;">
                        @php
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
                        @endphp

                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light text-center sticky-top" style="position: sticky; top: 0; z-index: 1;">
  <tr>
    <th style="width:72px;">#</th>

    <th class="{{ $sortClass('name') }} text-start">
      <a href="{{ $sortUrl('name') }}">รายการ</a>
    </th>

    <th class="{{ $sortClass('book_qty') }} text-end">
      <a class="d-block text-end" href="{{ $sortUrl('book_qty') }}">ยอดคงคลัง</a>
    </th>

    <th class="{{ $sortClass('remain') }} text-end">
      <a class="d-block text-end" href="{{ $sortUrl('remain') }}">คงเหลือ</a>
    </th>

    <th class="{{ $sortClass('unit') }}">
      <a href="{{ $sortUrl('unit') }}">หน่วยนับ</a>
    </th>

    <th class="{{ $sortClass('used_qty') }} text-end">
      <a class="d-block text-end" href="{{ $sortUrl('used_qty') }}">จำนวนที่ใช้ไป</a>
    </th>

    <th class="{{ $sortClass('category') }} text-start">
      <a href="{{ $sortUrl('category') }}">หมวดหมู่</a>
    </th>

    <th style="width:160px;">จัดการ</th>
  </tr>
</thead>

                        <tbody>
                        @php
                            $offset = (is_object($stocks) && method_exists($stocks, 'firstItem') && !is_null($stocks->firstItem()))
                            ? ((int)$stocks->firstItem() - 1)
                            : 0;
                        @endphp

                        @if($stocks && (method_exists($stocks,'count') ? $stocks->count() : count($stocks)))
                            @foreach($stocks as $index => $s)
                            <tr>
                                <td class="text-center">{{ $offset + $index + 1 }}</td>
                                <td class="fw-semibold">{{ $s->name }}</td>

                                <td class="text-end">{{ number_format($s->book_qty) }}</td>
                                <td class="text-end {{ $s->remain <= 0 ? 'text-danger fw-semibold' : '' }}">{{ number_format($s->remain) }}</td>

                                <td class="text-center">{{ $s->unit }}</td>
                                <td class="text-end">{{ number_format($s->used_qty_int) }}</td>
                                <td>{{ $s->category?->name ?? '-' }}</td>

                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">

                                        {{-- เติม (รับเข้า) --}}
                                        <button class="btn btn-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#inModal{{ $s->id }}">
                                        เติม
                                        </button>

                                        {{-- เบิกออก --}}
                                        <button class="btn btn-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#outModal{{ $s->id }}"
                                                @disabled(($s->remain ?? 0) <= 0)>
                                        เบิก
                                        </button>

                                        {{-- ลบรายการ --}}
                                        <button class="btn btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#delModal{{ $s->id }}">
                                        ลบ
                                        </button>
                                    </div>

                                    {{-- Modal: เติม --}}
                                    <div class="modal fade" id="inModal{{ $s->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                        <form class="modal-content" method="POST" action="{{ route('admin.stock.in', $s) }}">
                                            @csrf
                                            <div class="modal-header">
                                            <h5 class="modal-title">เติมสต็อก: {{ $s->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                            <div class="mb-2">
                                                <label class="form-label">จำนวน ({{ $s->unit }})</label>
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

                                    {{-- Modal: เบิก --}}
                                    <div class="modal fade" id="outModal{{ $s->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                        <form class="modal-content" method="POST" action="{{ route('admin.stock.out', $s) }}">
                                            @csrf
                                            <div class="modal-header">
                                            <h5 class="modal-title">เบิก: {{ $s->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                            @php
                                                // ใช้อะไรที่คุณมี: ถ้าใช้โมเดล accessor ให้เรียก $s->remain ได้เลย
                                                $remain = (int)($s->remain ?? 0);
                                            @endphp
                                            <div class="mb-2">
                                                <label class="form-label">จำนวน (คงเหลือ {{ number_format($remain) }} {{ $s->unit }})</label>
                                                <input type="number" name="qty" min="1" max="{{ $remain }}" class="form-control" required>
                                            </div>
                                            <div>
                                                <label class="form-label">หมายเหตุ</label>
                                                <input type="text" name="note" class="form-control" placeholder="เช่น งาน PM ฝ่ายช่าง">
                                            </div>
                                            </div>
                                            <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                            <button class="btn btn-warning" @disabled($remain <= 0)>บันทึก</button>
                                            </div>
                                        </form>
                                        </div>
                                    </div>

                                    {{-- Modal: ลบ --}}
                                    <div class="modal fade" id="delModal{{ $s->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                        <form class="modal-content" method="POST" action="{{ route('admin.stock.destroy', $s) }}">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header">
                                            <h5 class="modal-title">ลบรายการ</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                            ยืนยันลบ: <strong>{{ $s->name }}</strong>
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
                            @endforeach
                        @else
                            <tr>
                            <td colspan="8" class="text-center text-muted py-5">ยังไม่มีข้อมูลสต็อก</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    </div>

                    {{-- เพจจิเนชัน (แสดงครั้งเดียวพอ) --}}
                    @if(is_object($stocks) && method_exists($stocks, 'links'))
                    <div class="p-3">
                        {{ $stocks->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>

        </div>
      </div>
    </div>
  </div>

  {{-- Modal: เพิ่ม --}}
  <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" method="POST" action="{{ route('admin.stock.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">เพิ่มรายการสต็อก</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">รายการ</label>
            <input name="name" class="form-control @error('name') is-invalid @enderror" required autofocus>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="mb-2">
            <label class="form-label">หมวดหมู่</label>
            <input name="category_name" class="form-control @error('category_name') is-invalid @enderror"
                   placeholder="เช่น วัสดุสิ้นเปลือง" required>
            @error('category_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="mb-2">
            <label class="form-label">หน่วยนับ</label>
            <input name="unit" class="form-control @error('unit') is-invalid @enderror"
                   placeholder="กล่อง / ชิ้น / ม้วน" required>
            @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div>
            <label class="form-label">ยอดตั้งต้น</label>
            <input type="number" name="qty_open" class="form-control @error('qty_open') is-invalid @enderror" min="0" value="0">
            @error('qty_open') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button class="btn btn-success">บันทึก</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal: Import --}}
  <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" method="POST" action="{{ route('admin.stock.import') }}" enctype="multipart/form-data">
        @csrf
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
          <a href="{{ route('admin.stock.export') }}" class="btn btn-link">ดาวน์โหลดตัวอย่าง (Export)</a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button class="btn btn-primary">นำเข้า</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal fade" id="inModalShared" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="POST" id="inForm" action="#">
      @csrf
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


  {{-- UX เสริม --}}
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

</x-app-layout>
