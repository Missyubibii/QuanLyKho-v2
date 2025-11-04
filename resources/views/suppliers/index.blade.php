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
                <a href="{{ route('admin.suppliers.create') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                        </path>
                    </svg>
                    Thêm mới
                </a>
            @endcan
        </div>

<div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 items-end">
        {{-- Ô TÌM KIẾM (Bọc trong một div relative) --}}
        <div class="md:col-span-2 lg:col-span-2 relative">
            <div x-data="dynamicSearch({
                searchUrl: '/admin/suppliers/search-json',
                placeholder: 'Tìm kiếm theo tên, email, người liên hệ...',
                displayKey: 'name',
                secondaryKey: 'contact_person',
                onSelect(item) {
                    window.location.href = `/admin/suppliers/${item.id}`;
                }
            })">
                <input type="text" :placeholder="placeholder" x-model="searchTerm" @input.debounce.300ms="search()"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10"
                    autocomplete="off">

                <!-- Icon loading -->
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg x-show="isLoadingSearch" class="animate-spin h-5 w-5 text-gray-400"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Dropdown kết quả tìm kiếm -->
                <div x-show="searchResults.length > 0 && searchTerm.length > 1" @click.away="searchResults = []"
                    x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    style="display: none;"
                    class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 rounded-md shadow-lg max-h-60 overflow-auto border border-gray-200 dark:border-gray-600">
                    <ul>
                        <template x-for="item in searchResults" :key="item.id">
                            <li @click="selectItem(item)"
                                class="cursor-pointer px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="font-medium" x-text="item[displayKey]"></div>
                                <div x-show="secondaryKey" class="text-xs text-gray-500" x-text="item[secondaryKey]"></div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Dropdown TRẠNG THÁI --}}
        <div>
            <label for="is_active" class="block text-sm font-medium  text-gray-700 dark:text-gray-300 sr-only">Trạng thái</label>
            <select id="is_active" x-model="activeFilter" @change="applyFilters()" {{-- Đổi thành applyFilters --}}
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tất cả trạng thái</option>
                <option value="1">Hoạt động</option>
                <option value="0">Không hoạt động</option>
            </select>
        </div>

        {{-- Nút Reset --}}
        <a href="{{ route('admin.suppliers.index') }}"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center transition duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </a>
    </div>
</div>

        <div x-show="showBulkActions" x-transition style="display: none;" {{-- Chống nháy --}}
            class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-sm text-blue-800 dark:text-blue-200">Đã chọn <span
                        x-text="selectedSuppliers.length"></span> nhà cung cấp</span>
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
                                    <input
                                        class="form-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                        type="checkbox" :checked="selectAll" :indeterminate="selectAllIndeterminate"
                                        @change="toggleSelectAll()">
                                </th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Tên</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Liên hệ
                                </th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Trạng
                                    thái
                                </th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($suppliers as $supplier)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-4">
                                        {{-- Checkbox cho từng dòng --}}
                                        <input
                                            class="form-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 supplier-checkbox"
                                            type="checkbox" :value="{{ $supplier->id }}"
                                            :checked="selectedSuppliers.includes({{ $supplier->id }})"
                                            @change="toggleSupplierSelection({{ $supplier->id }})">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $supplier->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $supplier->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $supplier->contact_person ?? '—' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $supplier->phone ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($supplier->is_active)
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">Hoạt
                                                động</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Không
                                                hoạt động</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                        {{-- Nút Xem --}}
                                        <a href="{{ route('admin.suppliers.show', $supplier) }}"
                                            class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Xem</a>
                                        {{-- Nút Sửa --}}
                                        @can('update', $supplier)
                                            <a href="{{ route('admin.suppliers.edit', $supplier) }}"
                                                class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Sửa</a>
                                        @endcan
                                        {{-- Nút Xóa --}}
                                        @can('delete', $supplier)
                                            <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhà cung cấp này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Xóa</button>
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
                    {{ $suppliers->withQueryString()->links() }}
                </div>
        </div>
    </div>
@endsection
