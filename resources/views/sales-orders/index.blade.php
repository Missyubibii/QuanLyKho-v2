@extends('layouts.app')

@section('title', 'Danh sách Phiếu Xuất Kho')

@php
    // Dữ liệu ban đầu cho Alpine
    $initialData = [
        'search' => request('search', ''),
        'statusFilter' => request('status', ''),
        'customerFilter' => request('customer_id', ''),
        'dateFromFilter' => request('date_from', ''),
        'dateToFilter' => request('date_to', ''),
        'allSoIds' => $salesOrders->pluck('id')->toArray(), // Lấy IDs của các SO trên trang hiện tại
    ];
@endphp

@section('content')
    <div x-data="salesOrderPage(@js($initialData))" class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Phiếu Xuất Kho</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Quản lý các đơn hàng xuất kho.</p>
            </div>
            @can('create', \App\Models\SalesOrder::class)
                <a href="{{ route('admin.sales-orders.create') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tạo Phiếu Xuất
                </a>
            @endcan
        </div>

        {{-- Filter Card --}}
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                {{-- Dynamic Search Box --}}
                <div class="md:col-span-2 lg:col-span-2 relative">
                    <div x-data="dynamicSearch({
                        searchUrl: '{{ route('admin.sales-orders.search.json') }}',
                        placeholder: 'Tìm kiếm theo mã SO, tên khách hàng...',
                        displayKey: 'so_code',
                        secondaryKey: 'customer_name',
                        onSelect(item) {
                            window.location.href = item.url;
                        }
                    })">
                        <input type="text" :placeholder="placeholder" x-model="searchTerm" @input.debounce.300ms="search()"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10"
                            autocomplete="off">
                        <!-- Icon loading và dropdown giữ nguyên -->
                    </div>
                </div>

                {{-- Dropdown LỌC THEO KHÁCH HÀNG --}}
                <div>
                    <select x-model="customerFilter" @change="searchSOs()"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Tất cả KH</option>
                        @foreach($customers as $id => $name)
                            <option value="{{ $id }}" {{ request('customer_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Dropdown LỌC THEO TRẠNG THÁI --}}
                <div>
                    <select x-model="statusFilter" @change="searchSOs()"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Đã giao hàng</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div x-show="showBulkActions" x-transition style="display: none;"
            class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-sm text-blue-800 dark:text-blue-200">Đã chọn <span x-text="selectedSOs.length"></span> phiếu xuất</span>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="bulkDeleteSOs()" :disabled="isLoading"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm disabled:opacity-50 transition duration-300">
                    <span x-show="!isLoading">Xóa đã chọn</span>
                    <span x-show="isLoading">Đang xử lý...</span>
                </button>
                <button @click="selectedSOs = [];" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-3 rounded text-sm transition duration-300">Hủy</button>
            </div>
        </div>

        {{-- Main Table Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input class="form-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                    type="checkbox" :checked="selectAll" :indeterminate="selectAllIndeterminate" @change="toggleSelectAll()">
                            </th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Mã SO</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Khách hàng</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Ngày đặt</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Tổng tiền</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Trạng thái</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($salesOrders as $order)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4">
                                    <input class="form-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                        type="checkbox" :value="{{ $order->id }}" :checked="selectedSOs.includes({{ $order->id }})"
                                        @change="toggleSOSelection({{ $order->id }})">
                                </td>
                                <td class="px-6 py-4 font-semibold text-indigo-600 dark:text-indigo-400">
                                    <a href="{{ route('admin.sales-orders.show', $order) }}">{{ $order->so_code }}</a>
                                </td>
                                <td class="px-6 py-4 text-gray-800 dark:text-gray-200">{{ $order->customer?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $order->order_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-right text-gray-800 dark:text-gray-200 font-medium">
                                    {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                </td>
                                <td class="px-6 py-4">
                                    <span @class([
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' => $order->status == 'pending',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' => $order->status == 'processing',
                                        'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => $order->status == 'shipped',
                                        'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' => $order->status == 'cancelled',
                                    ])>
                                        {{ match ($order->status) { 'pending' => 'Chờ xử lý', 'processing' => 'Đang xử lý', 'shipped' => 'Đã giao hàng', 'cancelled' => 'Đã hủy', default => $order->status} }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                    <a href="{{ route('admin.sales-orders.show', $order) }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Xem</a>
                                    @if($order->status == 'pending')
                                        @can('update', $order)
                                            <a href="{{ route('admin.sales-orders.edit', $order) }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Sửa</a>
                                        @endcan
                                        @can('delete', $order)
                                            <form action="{{ route('admin.sales-orders.destroy', $order) }}" method="POST" class="inline" onsubmit="return confirm('Xóa phiếu xuất này?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Xóa</button>
                                            </form>
                                        @endcan
                                    @endif
                                    @if($order->status == 'pending' || $order->status == 'processing')
                                        @can('approve', $order)
                                            <form action="{{ route('admin.sales-orders.ship', $order) }}" method="POST" class="inline" onsubmit="return confirm('Xác nhận đã giao đủ hàng cho phiếu này?')">
                                                @csrf
                                                <button type="submit" class="font-medium text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">Giao hàng</button>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-6 text-gray-500 dark:text-gray-400">Không có phiếu xuất nào phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $salesOrders->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
