@extends('layouts.app')

@section('title', 'Chi tiết: ' . $customer->name)

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $customer->name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                ID: {{ $customer->id }} |
                @if($customer->is_active)
                    <span class="text-green-600 font-medium">Đang hoạt động</span>
                @else
                    <span class="text-gray-500 font-medium">Không hoạt động</span>
                @endif
            </p>
        </div>
        <div class="flex-shrink-0 flex items-center space-x-2">
            <a href="{{ route('admin.customers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 font-bold py-2 px-4 rounded-lg transition duration-300">
                Quay lại
            </a>
             @can('update', $customer)
                <a href="{{ route('admin.customers.edit', $customer) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
                    Chỉnh sửa
                </a>
            @endcan
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
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-200">{{ $customer->contact_person ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                        <p class="mt-1 text-base font-semibold text-indigo-600 dark:text-indigo-400">{{ $customer->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Điện thoại</label>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-200">{{ $customer->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Địa chỉ</label>
                        <p class="mt-1 text-base text-gray-800 dark:text-gray-200">{{ $customer->address ?? '—' }}</p>
                    </div>
                     {{-- Thêm các trường khác nếu có --}}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ghi chú</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $customer->notes ?? 'Không có ghi chú.' }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Đơn hàng gần đây</h3>
                </div>
                 <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Mã ĐH</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Ngày đặt</th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">Tổng tiền</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            {{-- Controller đã load 'salesOrders' --}}
                            @forelse ($customer->salesOrders as $order)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 font-medium text-indigo-600 dark:text-indigo-400">
                                        <a href="{{ route('admin.sales-orders.show', $order) }}">{{ $order->order_code ?? $order->id }}</a> {{-- Giả sử có order_code --}}
                                    </td>
                                    <td class="px-4 py-3">{{ $order->order_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($order->total_amount ?? 0, 0, ',', '.') }} đ</td> {{-- Giả sử có total_amount --}}
                                    <td class="px-4 py-3">
                                        {{-- Hiển thị trạng thái của SalesOrder --}}
                                        @switch($order->status)
                                            @case('pending') <span class="text-yellow-600">Chờ xử lý</span> @break
                                            @case('shipped') <span class="text-green-600">Đã giao</span> @break
                                            @case('cancelled') <span class="text-red-600">Đã hủy</span> @break
                                            @default <span class="text-gray-500">{{ $order->status }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        Chưa có đơn hàng nào gần đây.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Thông tin tài chính</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Công nợ hiện tại</label>
                        <p class="mt-1 text-2xl font-bold {{ ($customer->current_debt ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-200' }}">
                            {{ number_format($customer->current_debt ?? 0, 0, ',', '.') }} đ
                        </p>
                    </div>
                    {{-- Thêm các thông tin tài chính khác nếu có, ví dụ: Tổng doanh thu từ khách hàng này --}}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Thông tin khác</h3>
                </div>
                <div class="p-6 space-y-4">
                    {{-- Thêm các trường khác nếu có --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Ngày tạo</label>
                        <p class="mt-1 text-base text-gray-800 dark:text-gray-200">{{ $customer->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
