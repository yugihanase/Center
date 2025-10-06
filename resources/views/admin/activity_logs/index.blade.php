{{-- resources/views/admin/activity_logs/index.blade.php --}}
<x-app-layout>
  <div class="container py-3">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
        <span>บันทึกกิจกรรม</span>
        <form method="get" class="d-flex gap-2">
          <input type="number" name="perPage" value="{{ $perPage ?? 50 }}" class="form-control form-control-sm" style="width:100px">
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
            @forelse($logs as $row)
              <tr>
                <td class="text-nowrap">{{ $row->ts?->format('Y-m-d H:i:s') }}</td>
                <td>{{ $row->user }}</td>
                <td>
                  <span class="badge bg-secondary text-uppercase">{{ $row->event }}</span>
                  @if($row->source === 'assignment')
                    <span class="ms-1 badge bg-info">assignment</span>
                    @elseif($row->source === 'stock')
                    <span class="ms-1 badge bg-success">stock</span>
                  @endif
                </td>
                <td>{{ $row->subject }}</td>
                <td>
                  @if($row->detail) {{ $row->detail }} @endif
                  @if($row->props)
                    <details class="mt-1">
                      <summary class="small text-muted">properties</summary>
                      <pre class="mb-0 small">{{ json_encode($row->props, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</pre>
                    </details>
                  @endif
                </td>
                <td class="text-nowrap">{{ $row->ip ?? '-' }}</td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted">— ไม่มีข้อมูล —</td></tr>
            @endforelse
          </tbody>
        </table>
        {{ $logs->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</x-app-layout>
