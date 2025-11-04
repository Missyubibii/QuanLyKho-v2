@php
    // Chuẩn bị dữ liệu ban đầu
    $initialData = [
        'search' => request('search', ''),
        'activeFilter' => request('is_active', ''), // Dùng is_active
        'allSupplierIds' => $suppliers->pluck('id')->toArray(), // Lấy ID suppliers
    ];
@endphp

@extends('layouts.app')

@section('title', 'Danh sách Nhà cung cấp')

@section('content')
{{-- Khởi tạo Alpine component cho suppliers --}}
<div x-data="supplierIndexPage(@js($initialData))" class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Danh sách Nhà cung cấp</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Quản lý thông tin các nhà cung cấp.</p>
        </div>
        {{-- Nút Thêm --}}
        @can('create', \App\Models\Supplier::class)
            <a href="{{ route('admin.suppliers.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Thêm mới
            </a>
        @endcan
    </div>

    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
         {{-- Form lọc, không cần thẻ <form> vì JS xử lý --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2 lg:col-span-2">
                 <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sr-only">Tìm kiếm</label>
                <input type="text"
                       id="search"
                       x-model.debounce.300ms="search" {{-- Thêm debounce --}}
                       @keyup="searchSuppliers()" {{-- Trigger khi gõ --}}
                       placeholder="Tìm kiếm theo tên, email, người liên hệ..."
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                 <label for="is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sr-only">Trạng thái</label>
                <select id="is_active"
                        x-model="activeFilter"
                        @change="searchSuppliers()" {{-- Trigger khi chọn --}}
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1">Hoạt động</option>
                    <option value="0">Không hoạt động</option>
                </select>
            </div>
             {{-- Nút Tìm kiếm không cần thiết nếu dùng trigger tự động --}}
            {{-- <button @click="searchSuppliers()" class="bg-indigo-600 ...">Tìm</button> --}}
            {{-- Nút Reset Filter --}}
            <a href="{{ route('admin.suppliers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 font-bold py-2 px-4 rounded-lg flex items-center justify-center transition duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9H4V4M4 12l4 4m0 0l4-4m-4 4V4"></path></svg>
            </a>
        </div>
    </div>

    <div x-show="showBulkActions" x-transition
         style="display: none;" {{-- Chống nháy --}}
         class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-between">
        <div class="flex items-center">
            <span class="text-sm text-blue-800 dark:text-blue-200">Đã chọn <span x-text="selectedSuppliers.length"></span> nhà cung cấp</span>
        </div>
        <div class="flex items-center space-x-2">
            <button @click="bulkDelete()" :disabled="isLoading"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm disabled:opacity-50 transition duration-300">
                <span x-show="!isLoading">Xóa đã chọn</span>
                <span x-show="isLoading">Đang xử lý...</span>
            </button>
            <button @click="selectedSuppliers = []; selectAll = false;"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-3 rounded text-sm transition duration-300">
                Hủy
            </button>
        </div>
    </div>

    <div x-show="isLoading" x-transition
         style="display: none;" {{-- Chống nháy --}}
         class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
         <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-yellow-800 dark:text-yellow-200">Đang xử lý...</span>
         </div>
         <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
            <div class="bg-yellow-600 h-2.5 rounded-full animate-pulse" style="width: 100%"></div>
         </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        {{-- Form bulk delete bao quanh table --}}
        {{-- Không cần form này nếu dùng fetch API trong JS --}}
        {{-- <form id="bulkDeleteForm" method="POST" action="{{ route('admin.suppliers.bulk-delete') }}"> --}}
        {{-- @csrf --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input class="form-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                    type="checkbox"
                                    :checked="selectAll"
                                    :indeterminate="selectAllIndeterminate"
                                    @change="toggleSelectAll()">
                            </th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Tên</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Liên hệ</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Trạng thái</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4">
                                {{-- Checkbox cho từng dòng --}}
                                <input class="form-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 supplier-checkbox"
                                    type="checkbox"
                                    :value="{{ $supplier->id }}"
                                    :checked="selectedSuppliers.includes({{ $supplier->id }})"
                                    @change="toggleSupplierSelection({{ $supplier->id }})">
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $supplier->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $supplier->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $supplier->contact_person ?? '—' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $supplier->phone ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($supplier->is_active)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">Hoạt động</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Không hoạt động</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                {{-- Nút Xem --}}
                                <a href="{{ route('admin.suppliers.show', $supplier) }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Xem</a>
                                {{-- Nút Sửa --}}
                                @can('update', $supplier)
                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Sửa</a>
                                @endcan
                                {{-- Nút Xóa --}}
                                @can('delete', $supplier)
                                    <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhà cung cấp này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Xóa</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Không có nhà cung cấp nào phù hợp.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{-- Giữ lại query string khi chuyển trang --}}
                {{ $suppliers->withQueryString()->links() }}
            </div>
        {{-- </form> --}} {{-- Đóng form nếu dùng submit truyền thống --}}
    </div>
</div>
@endsection
