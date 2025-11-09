@extends('layouts.app')

@section('title', 'Chi tiết Biến động Tồn kho: #' . $inventoryMovement->id)

@section('content')
    <div class="p-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Chi tiết Biến động Tồn kho</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    ID: {{ $inventoryMovement->id }} |
                    Ngày: {{ $inventoryMovement->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('admin.inventory-movements.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 font-bold py-2 px-4 rounded-lg transition duration-300">
                    Quay lại
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Cột chính: Chi tiết biến động --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Thông tin biến động</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Loại biến
                                    động</label>
                                <div class="mt-1">
                                    <span @class([
                                        'px-3 py-1 text-sm font-medium rounded-full',
                                        'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => $inventoryMovement->type === 'in',
                                        'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' => $inventoryMovement->type === 'out',
                                    ])>
                                        {{ $inventoryMovement->type === 'in' ? 'Nhập kho' : 'Xuất kho' }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Số lượng thay
                                    đổi</label>
                                <p
                                    class="mt-1 text-lg font-bold {{ $inventoryMovement->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $inventoryMovement->type === 'in' ? '+' : '-' }}{{ $inventoryMovement->quantity_change }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Tồn kho sau khi
                                    thay đổi</label>
                                <p class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100">
                                    {{ $inventoryMovement->quantity_after }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Người thực
                                    hiện</label>
                                <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $inventoryMovement->user?->name ?? 'Hệ thống' }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Ghi chú</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                {{ $inventoryMovement->notes ?? 'Không có ghi chú.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cột phụ: Thông tin liên quan --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Thông tin sản phẩm --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Sản phẩm liên quan</h3>
                    </div>
                    <div class="p-6">
                        @if($inventoryMovement->product)
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Tên sản
                                        phẩm</label>
                                    <a href="{{ route('admin.products.show', $inventoryMovement->product) }}"
                                        class="mt-1 text-base font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $inventoryMovement->product->name }}
                                    </a>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">SKU</label>
                                    <p class="mt-1 text-base text-gray-800 dark:text-gray-200">
                                        {{ $inventoryMovement->product->sku }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Tồn kho hiện
                                        tại</label>
                                    <p class="mt-1 text-base text-gray-800 dark:text-gray-200">
                                        {{ $inventoryMovement->product->quantity }} {{ $inventoryMovement->product->unit }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">Sản phẩm này đã bị xóa.</p>
                        @endif
                    </div>
                </div>

                {{-- Thông tin nguồn gốc (PO/SO) --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Nguồn gốc</h3>
                    </div>
                    <div class="p-6">
    @if($inventoryMovement->source)
        <div class="space-y-2">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Loại phiếu</label>
                <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-200">
                    {{-- SỬA: Dùng accessor 'source_type_display' --}}
                    {{ $inventoryMovement->source_type_display }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Mã phiếu</label>
                {{-- SỬA: Dùng accessor 'source_url' và 'source_code' --}}
                <a href="{{ $inventoryMovement->source_url }}" class="mt-1 text-base font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ $inventoryMovement->source_code }}
                </a>
            </div>
        </div>
    @else
        <p class="text-gray-500">Không có thông tin nguồn gốc.</p>
    @endif
</div>
                </div>
            </div>
        </div>
    </div>
@endsection
