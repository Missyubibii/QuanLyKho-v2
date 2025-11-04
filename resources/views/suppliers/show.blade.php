@extends('layouts.app')

@section('title', 'Chi tiết: ' . $supplier->name)

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $supplier->name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                ID: {{ $supplier->id }} |
                @if($supplier->is_active)
                    <span class="text-green-600 font-medium">Đang hoạt động</span>
                @else
                    <span class="text-gray-500 font-medium">Không hoạt động</span>
                @endif
            </p>
        </div>
        <div class="flex-shrink-0 flex items-center space-x-2">
            <a href="{{ route('admin.suppliers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 font-bold py-2 px-4 rounded-lg transition duration-300">
                Quay lại
            </a>
            <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
                Chỉnh sửa
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Thông tin liên hệ</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Người liên hệ</label>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-200">{{ $supplier->contact_person ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                        <p class="mt-1 text-base font-semibold text-indigo-600 dark:text-indigo-400">{{ $supplier->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Điện thoại</label>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-200">{{ $supplier->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Địa chỉ</label>
                        <p class="mt-1 text-base text-gray-800 dark:text-gray-200">{{ $supplier->address ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ghi chú</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $supplier->notes ?? 'Không có ghi chú.' }}</p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Thông tin thêm</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Mã số thuế</R>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-200">{{ $supplier->tax_code ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Website</label>
                        @if($supplier->website)
                            <a href="{{ $supplier->website }}" target="_blank" class="mt-1 text-base font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ $supplier->website }}
                            </a>
                        @else
                            <p class="mt-1 text-base text-gray-800 dark:text-gray-200">—</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Ngày tạo</label>
                        <p class="mt-1 text-base text-gray-800 dark:text-gray-200">{{ $supplier->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            {{-- Bạn cần truyền biến $supplier->products từ controller để phần này hoạt động --}}
            @if(isset($supplier->products))
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Sản phẩm liên quan</h3>
                </div>
                <div class="p-3">
                    <ul class="max-h-60 overflow-y-auto space-y-2">
                        @forelse($supplier->products as $product)
                            <li class="px-3 py-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <a href="{{ route('admin.products.show', $product) }}" class="block">
                                    <p class="font-medium text-sm text-gray-800 dark:text-gray-200">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $product->sku }}</p>
                                </a>
                            </li>
                        @empty
                            <li class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                Không có sản phẩm nào từ nhà cung cấp này.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
