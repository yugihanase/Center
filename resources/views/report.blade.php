<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ระบบแจ้งซ่อมอุปกรณ์') }}
        </h2>
    </x-slot>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
      <div class="card shadow" style="max-width: 480px; width: 100%;">
        <div class="card-header text-center fw-semibold">
          แจ้งซ่อมอุปกรณ์
        </div>
        <div class="card-body">
            {{-- resources/views/report/create.blade.php --}}
          @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <form method="POST" action="{{ route('report.store') }}">
            @csrf

            <div class="mb-3">
              <label for="device_address" class="form-label">ที่อยู่อุปกรณ์</label>
              <input
                type="text"
                id="device_address"
                name="device_address"
                class="form-control @error('device_address') is-invalid @enderror"
                value="{{ old('device_address') }}"
                required
              >
              @error('device_address')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="device_list" class="form-label">รายการอุปกรณ์</label>
              <input
                type="text"
                id="device_list"
                name="device_list"
                class="form-control @error('device_list') is-invalid @enderror"
                value="{{ old('device_list') }}"
                required
              >
              @error('device_list')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="detail" class="form-label">แจ้งรายละเอียด</label>
              <textarea
                id="detail"
                name="detail"
                rows="3"
                class="form-control @error('detail') is-invalid @enderror"
                placeholder="รายละเอียด"
                required
              >{{ old('detail') }}</textarea>
              @error('detail')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <button type="submit" class="btn btn-primary w-100">ยืนยัน</button>
          </form>
        </div>
        
        <div class="card-footer text-muted text-center">
          <a href="{{ url('/') }}">กลับหน้าแรก</a>
        </div>
      </div>
    </div>
</x-app-layout>