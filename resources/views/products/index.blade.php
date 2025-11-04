{{-- resources/views/products/index.blade.php --}}

@php
    // Chuẩn bị dữ liệu ban đầu để truyền vào component Alpine.js
    $initialData = [
        'search' => request('search', ''),
        'categoryFilter' => request('category_id', ''),
        'supplierFilter' => request('supplier_id', ''),
        'statusFilter' => request('status', ''),
        'allProductIds' => $products->pluck('id')->toArray(),
    ];
@endphp


@extends('layouts.app')

@section('title', 'Quản lý Sản phẩm')

@section('content')
    <div x-data="productIndexPage(@js($initialData))" class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <!-- Header với Dashboard và Thông báo -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Quản lý Sản phẩm</h1>
                <div class="mt-2 flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Tổng: {{ $products->total() }} sản phẩm</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Tồn kho thấp: {{ $lowStockCount }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Nút Thêm sản phẩm -->
                @can('create', \App\Models\Product::class)
                    <a href="{{ route('admin.products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Thêm sản phẩm
                    </a>
                @endcan
            </div>
        </div>

        <!-- Bộ lọc nâng cao -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text"
                            x-model="search"
                            @keyup.debounce.300ms="searchProducts()"
                            placeholder="Tìm theo tên, SKU, nhà cung cấp..."
                            class="w-full form-input rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-indigo-500">
                </div>

                <div class="min-w-[150px]">
                    <select x-model="categoryFilter" @change="searchProducts()" class="w-full form-select rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-indigo-500">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[150px]">
                    <select x-model="supplierFilter" @change="searchProducts()" class="w-full form-select rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-indigo-500">
                        <option value="">Tất cả nhà cung cấp</option>
                        @foreach($suppliers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[150px]">
                    <select x-model="statusFilter" @change="searchProducts()" class="w-full form-select rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-indigo-500">
                        <option value="">Tất cả trạng thái</option>
                        <option value="in_stock">Còn hàng</option>
                        <option value="out_of_stock">Hết hàng</option>
                        <option value="maintenance">Bảo trì</option>
                    </select>
                </div>

                <button @click="searchProducts()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Bulk Actions (Chỉ hiện khi có sản phẩm được chọn) -->
        <div x-show="showBulkActions" x-transition
            style="display: none;"
            class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-sm text-blue-800 dark:text-blue-200">Đã chọn <span x-text="selectedProducts.length"></span> sản phẩm</span>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="bulkDelete()" :disabled="isLoading"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm disabled:opacity-50 transition duration-300">
                    <span x-show="!isLoading">Xóa đã chọn</span>
                    <span x-show="isLoading">Đang xử lý...</span>
                </button>
                <button @click="selectedProducts = []; selectAll = false;"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-3 rounded text-sm transition duration-300">
                    Hủy
                </button>
            </div>
        </div>

        <!-- Progress Bar (Hiện khi đang xóa) -->
        <div x-show="isLoading" x-transition
            class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-yellow-800 dark:text-yellow-200">Đang xóa sản phẩm...</span>
                <span class="text-sm text-yellow-800 dark:text-yellow-200">Vui lòng đợi</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-yellow-600 h-2.5 rounded-full animate-pulse" style="width: 100%"></div>
            </div>
        </div>

        <!-- Bảng sản phẩm -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <!-- Trong phần <thead> của bảng -->
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox"
                                    :checked="selectAll"
                                    :indeterminate="selectAllIndeterminate"
                                    @change="toggleSelectAll()"
                                    class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-200">Sản phẩm</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-200">Danh mục</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-200">Nhà cung cấp</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-200">Giá bán</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-200">Tồn kho</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-200">Trạng thái</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-200">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($products as $product)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-4 py-3">
                                    <input type="checkbox"
                                            :checked="selectedProducts.includes({{ $product->id }})"
                                            @change='toggleProductSelection({{ $product->id }})'
                                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-md object-cover mr-3">
                                        @else
                                            <div class="w-10 h-10 rounded-md bg-gray-200 dark:bg-gray-600 mr-3 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $product->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $product->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $product->supplier?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($product->price_sell, 0, ',', '.') }} đ
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center">
                                        <span class="font-semibold {{ $product->quantity <= $product->min_stock && $product->min_stock > 0 ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' }}">
                                            {{ $product->quantity }}
                                        </span>
                                        @if($product->quantity <= $product->min_stock && $product->min_stock > 0)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @switch($product->status)
                                        @case('in_stock')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">Còn hàng</span>
                                            @break
                                        @case('out_of_stock')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">Hết hàng</span>
                                            @break
                                        @case('maintenance')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300">Bảo trì</span>
                                            @break
                                        @default
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">—</span>
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('admin.products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 font-medium">Xem</a>
                                    @can('update', $product)
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 font-medium">Sửa</a>
                                    @endcan
                                    @can('delete', $product)
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 font-medium">Xóa</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="mt-2">Không có sản phẩm nào.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Phân trang -->
        <div class="mt-4 px-4 py-2">
            {{ $products->links() }}
        </div>
    </div>
@endsection
