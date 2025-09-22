<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ระบบแจ้งซ่อมอุปกรณ์') }}
        </h2>
    </x-slot>
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

                                <form method="GET" action="{{ route('follow') }}" class="d-flex" role="search">
                                    <input
                                        type="search"
                                        class="form-control me-2"
                                        name="q"
                                        value="{{ $search ?? '' }}"
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
                                        @forelse ($users as $index => $user)
                                            <tr>
                                                <td>{{ $users->firstItem() + $index }}</td>
                                                <td>{{ $user->id}}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>แผนก IT</td>
                                                <td>notebook เปิดไม่ติด</td>
                                                <td>{{ optional($user->created_at)->format('Y-m-d H:i') }}</td>
                                                <td>รอดำเนินการ</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">ไม่พบข้อมูล</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end">
                                {{ $users->onEachSide(1)->links() }}
                            </div>
                        </div>
                    </div>
                </div> <!-- /.container -->
            </div> <!-- /.card-body -->
        </div>
    </div>
</x-app-layout>
