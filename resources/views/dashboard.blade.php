<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ระบบจัดการคน') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("รายงานแจ้งการใช้งาน") }}
                </div>
                <div class="p-6 text-gray-900">
                    <div class="mb-3">
                        <div class="card">
                            <h5 class="card-header">Featured</h5>
                            <div class="card-body">
                                <h5 class="card-title">Special title treatment</h5>
                                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                                <a href="#" class="btn btn-primary">Go somewhere</a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ url('report') }}">
                        <button type="button" class="btn btn-danger">แจ้งปัญหา</button>
                    </a>
                    <a href="{{ url('fig') }}">
                        <button type="button" class="btn btn-primary">รายการแจ้งปัญหา</button>
                    </a>
                    <button type="button" class="btn btn-warning">ปรับปรุง</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
