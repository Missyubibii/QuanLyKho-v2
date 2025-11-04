{{-- resources/views/products/show.blade.php --}}

@extends('layouts.app')

@section('title', 'Chi tiết sản phẩm: ' . $product->name)

@section('content')
<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $product->name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">SKU: {{ $product->sku }}</p>
        </div>
        <div class="flex-shrink-0 flex items-center space-x-2">
            <a href="{{ route('admin.products.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block -ml-1 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Quay lại
            </a>
            @can('update', $product)
                <a href="{{ route('admin.products.edit', $product) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Chỉnh sửa
                </a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Thông tin chung</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-auto object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                            @else
                                <div class="w-full h-40 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Mô tả</label>
                            <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">
                                {{ $product->description ?? 'Không có mô tả.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Lịch sử xuất & nhập kho</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Ngày</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Hành động</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Số lượng</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Kho sau CĐ</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Nguồn</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                {{-- @forelse ($product->inventoryMovements as $movement) --}}
                                {{-- BẠN CẦN TRUYỀN BIẾN NÀY TỪ CONTROLLER --}}
                                {{--
                                <tr>
                                    <td class="px-4 py-3">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        @if($movement->type == 'in')
                                            <span class="text-green-600 font-medium">Nhập kho</span>
                                        @else
                                            <span class="text-red-600 font-medium">Xuất kho</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $movement->quantity_change }}</td>
                                    <td class="px-4 py-3">{{ $movement->quantity_after }}</td>
                                    <td class="px-4 py-3">{{ $movement->source_type }}: {{ $movement->source_id }}</td>
                                </tr>
                                --}}
                                {{-- @empty --}}
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        Chưa có lịch sử biến động nào.
                                    </td>
                                </tr>
                                {{-- @endforelse --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6">

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Phân loại</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Trạng thái</label>
                            @switch($product->status)
                                @case('in_stock')
                                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">Còn hàng</span>
                                    @break
                                @case('out_of_stock')
                                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">Hết hàng</span>
                                    @break
                                @case('maintenance')
                                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300">Bảo trì</span>
                                    @break
                            @endswitch
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Danh mục</label>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $product->category?->name ?? '—' }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Nhà cung cấp</label>
                            @if($product->supplier)
                                <a href="{{ route('admin.suppliers.show', $product->supplier_id) }}"
                                    class="mt-1 text-base font-semibold text-indigo-600 ...">{{ $product->supplier->name }}</a>
                            @else
                                <p class="mt-1 text-base ...">N/A</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Giá & Tồn kho</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Giá nhập</label>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ number_format($product->price_buy, 0, ',', '.') }} đ</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Giá bán</label>
                            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($product->price_sell, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Số lượng tồn kho</label>
                            <span class="text-2xl font-bold {{ $product->quantity <= $product->min_stock && $product->min_stock > 0 ? 'text-red-600' : 'text-gray-800 dark:text-gray-200' }}">
                                {{ $product->quantity }}
                            </span>
                            <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">{{ $product->unit }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Tồn kho tối thiểu</label>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $product->min_stock }} {{ $product->unit }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
