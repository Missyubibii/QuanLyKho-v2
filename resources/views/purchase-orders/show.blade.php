@extends('layouts.app')

@section('title', 'Chi tiết Phiếu Nhập: ' . $purchaseOrder->po_code)

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $purchaseOrder->po_code }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Ngày đặt: {{ $purchaseOrder->order_date->format('d/m/Y') }} |
                {{-- Status Badge --}}
                <span @class([
                    'px-2 py-0.5 text-xs font-medium rounded-full',
                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' => $purchaseOrder->status == 'pending',
                    'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' => $purchaseOrder->status == 'processing',
                    'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => $purchaseOrder->status == 'completed',
                    'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' => $purchaseOrder->status == 'cancelled',
                ])>
                    {{ match ($purchaseOrder->status) { 'pending' => 'Chờ xử lý', 'processing' => 'Đang xử lý', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy', default => $purchaseOrder->status} }}
                </span>
            </p>
        </div>
        <div class="flex-shrink-0 flex items-center space-x-2">
            <a href="{{ route('admin.purchase-orders.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 font-bold py-2 px-4 rounded-lg transition duration-300">
                Quay lại
            </a>
            @if($purchaseOrder->status == 'pending')
                @can('update', $purchaseOrder)
                    <a href="{{ route('admin.purchase-orders.edit', $purchaseOrder) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
                        Chỉnh sửa
                    </a>
                @endcan
            @endif
            @if($purchaseOrder->status == 'pending' || $purchaseOrder->status == 'processing')
                @can('approve', $purchaseOrder)
                    <form action="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}" method="POST" class="inline" onsubmit="return confirm('Xác nhận đã nhận đủ hàng cho phiếu này?')">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
                            Xác nhận Nhận hàng
                        </button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Cột Trái: Danh sách sản phẩm (chiếm 2/3) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Danh sách sản phẩm</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Sản phẩm</th>
                                <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Số lượng</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Đơn giá</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($purchaseOrder->items as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.products.show', $item->product_id) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">{{ $item->product?->name ?? 'N/A' }}</a>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">SKU: {{ $item->product?->sku ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-800 dark:text-gray-200">{{ $item->quantity }} {{ $item->product?->unit }}</td>
                                    <td class="px-6 py-4 text-right text-gray-800 dark:text-gray-200">{{ number_format($item->price, 0, ',', '.') }} đ</td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-gray-100">
                                        {{ number_format($item->subtotal, 0, ',', '.') }} đ
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-gray-500 dark:text-gray-400">Phiếu nhập này không có sản phẩm nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 font-bold">
                                <td colspan="3" class="px-6 py-4 text-right text-gray-700 dark:text-gray-200">Tổng cộng:</td>
                                <td class="px-6 py-4 text-right text-lg text-gray-900 dark:text-gray-100">
                                    {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Cột Phải: Thông tin chung (chiếm 1/3) --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Thông tin Phiếu nhập</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nhà cung cấp</label>
                        @if($purchaseOrder->supplier)
                            <a href="{{ route('admin.suppliers.show', $purchaseOrder->supplier_id) }}" class="mt-1 text-base font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ $purchaseOrder->supplier->name }}
                            </a>
                        @else
                            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-200">N/A</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Người tạo</label>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-200">{{ $purchaseOrder->user?->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Ngày đặt hàng</label>
                        <p class="mt-1 text-base text-gray-800 dark:text-gray-200">{{ $purchaseOrder->order_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Ngày dự kiến nhận</label>
                        <p class="mt-1 text-base text-gray-800 dark:text-gray-200">
                            {{ $purchaseOrder->expected_date ? $purchaseOrder->expected_date->format('d/m/Y') : '—' }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Ghi chú</label>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $purchaseOrder->notes ?? 'Không có ghi chú.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
