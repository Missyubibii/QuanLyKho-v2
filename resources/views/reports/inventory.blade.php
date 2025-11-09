@extends('layouts.app')

@section('title', 'Báo cáo Tồn kho')

@section('content')
<div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Báo cáo Tồn kho</h1>
        {{-- (Tùy chọn) Thêm nút Export CSV [cite: 365] --}}
    </div>

    {{-- Filters [cite: 365] --}}
    <form method="GET" action="{{ route('admin.reports.inventory') }}" class="mb-6">
        <div class="flex flex-wrap gap-4">
            <input type="text" name="search" placeholder="Tìm theo tên hoặc SKU..." value="{{ request('search') }}"
                   class="mt-1 block rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

            <select name="stock_status"
                    class="mt-1 block rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tất cả trạng thái</option>
                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Tồn kho thấp</option>
                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Hết hàng</option>
            </select>

            <button type="submit" class="mt-1 py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition duration-300">
                Lọc
            </button>
            <a href="{{ route('admin.reports.inventory') }}" class="mt-1 py-2 px-4 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">
                Xóa lọc
            </a>
        </div>
    </form>

    {{-- Bảng Báo cáo --}}
    <div class="overflow-x-auto rounded-lg shadow-lg">
        <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Sản phẩm</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">SKU</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Tồn kho</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Tồn tối thiểu</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Trạng thái</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-6 py-4 text-gray-800 dark:text-gray-200">{{ $product->name }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $product->sku }}</td>
                        <td class="px-6 py-4 text-center font-bold text-gray-800 dark:text-gray-200">{{ $product->quantity }}</td>
                        <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">{{ $product->min_stock }}</td>
                        <td class="px-6 py-4">
                            @if($product->quantity <= 0)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">Hết hàng</span>
                            @elseif($product->quantity <= $product->min_stock && $product->min_stock > 0)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300">Tồn thấp</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">Còn hàng</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-500 dark:text-gray-400">Không tìm thấy sản phẩm nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>
@endsection
