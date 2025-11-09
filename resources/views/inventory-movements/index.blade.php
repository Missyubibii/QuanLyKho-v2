@extends('layouts.app')

@section('title', 'Lịch sử Tồn kho')

@section('content')
    <div class="p-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Lịch sử Tồn kho</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Xem lại tất cả các biến động số lượng hàng tồn kho.
                </p>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <form method="GET" action="{{ route('admin.inventory-movements.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    {{-- Filter Sản phẩm --}}
                    <div>
                        <label for="product_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 sr-only">Sản phẩm</label>
                        <select id="product_id" name="product_id"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Tất cả sản phẩm</option>
                            @foreach($products as $id => $name)
                                <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Loại biến động --}}
                    <div>
                        <label for="type"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 sr-only">Loại</label>
                        <select id="type" name="type"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Tất cả loại</option>
                            <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Nhập kho</option>
                            <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Xuất kho</option>
                        </select>
                    </div>

                    {{-- Filter Khoảng ngày --}}
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sr-only">Từ
                            ngày</label>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 sr-only">Đến
                            ngày</label>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Nút Lọc và Reset --}}
                    <div class="flex space-x-2">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        </button>
                        <a href="{{ route('admin.inventory-movements.index') }}"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path><path d="M16 16h5v5"></path></svg>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Main Table Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Ngày</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Sản phẩm</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Loại</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">S.lượng</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Tồn Kho Sau</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Nguồn</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Người thực hiện</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($movements as $movement)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    {{ $movement->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $movement->product->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        SKU: {{ $movement->product->sku }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span @class([
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => $movement->type === 'in',
                                        'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' => $movement->type === 'out',
                                    ])>
                                        {{ $movement->type === 'in' ? 'Nhập' : 'Xuất' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-medium">
                                        {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-gray-100">
                                    {{ $movement->quantity_after }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($movement->source_url)
                                        <a href="{{ $movement->source_url }}"
                                        class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            {{ $movement->source_code }}
                                        </a>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $movement->notes }}</div>
                                    @else
                                        <span class="text-gray-500">Hệ thống</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    {{ $movement->user?->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.inventory-movements.show', $movement) }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-6 text-gray-500 dark:text-gray-400">
                                    Không có lịch sử biến động nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $movements->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
