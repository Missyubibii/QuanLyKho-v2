@extends('layouts.app')

@section('title', 'Trang tổng quan')

@section('content')
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <div class="mb-6 ">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                Chào mừng trở lại, {{ auth()->user()->name ?? 'Admin' }}!
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Đây là tổng quan nhanh về hệ thống kho của bạn.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex items-center space-x-4">
                <div
                    class="flex-shrink-0 w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600 dark:text-blue-300" width="25"
                        height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M2.97 12.92A2 2 0 0 0 2 14.63v3.24a2 2 0 0 0 .97 1.71l3 1.8a2 2 0 0 0 2.06 0L12 19v-5.5l-5-3-4.03 2.42Z">
                        </path>
                        <path d="m7 16.5-4.74-2.85"></path>
                        <path d="m7 16.5 5-3"></path>
                        <path d="M7 16.5v5.17"></path>
                        <path
                            d="M12 13.5V19l3.97 2.38a2 2 0 0 0 2.06 0l3-1.8a2 2 0 0 0 .97-1.71v-3.24a2 2 0 0 0-.97-1.71L17 10.5l-5 3Z">
                        </path>
                        <path d="m17 16.5-5-3"></path>
                        <path d="m17 16.5 4.74-2.85"></path>
                        <path d="M17 16.5v5.17"></path>
                        <path
                            d="M7.97 4.42A2 2 0 0 0 7 6.13v4.37l5 3 5-3V6.13a2 2 0 0 0-.97-1.71l-3-1.8a2 2 0 0 0-2.06 0l-3 1.8Z">
                        </path>
                        <path d="M12 8 7.26 5.15"></path>
                        <path d="m12 8 4.74-2.85"></path>
                        <path d="M12 13.5V8"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tổng số sản phẩm</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalProducts ?? 0 }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex items-center space-x-4">
                <div
                    class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cảnh báo tồn kho</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $lowStockCount ?? 0 }}</p>
                </div>
            </div>

            <a href="{{ route('admin.purchase-orders.index', ['status' => 'pending']) }}"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex items-center space-x-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-300">
                <div
                    class="flex-shrink-0 w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none"
                        class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"></path>
                        <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                        <path d="M2 15h10"></path>
                        <path d="m9 18 3-3-3-3"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Phiếu nhập chờ</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $pendingPurchases ?? 0 }}</p>
                </div>
            </a>

            <a href="{{ route('admin.sales-orders.index', ['status' => 'pending']) }}"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 flex items-center space-x-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-300">
                <div
                    class="flex-shrink-0 w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" xmlns="http://www.w3.org/2000/svg" width="25"
                        height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                        <path d="M4 7V4a2 2 0 0 1 2-2 2 2 0 0 0-2 2"></path>
                        <path d="M4.063 20.999a2 2 0 0 0 2 1L18 22a2 2 0 0 0 2-2V7l-5-5H6"></path>
                        <path d="m5 11-3 3"></path>
                        <path d="m5 17-3-3h10"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Đơn xuất chờ</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $pendingSales ?? 0 }}</p>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Sản phẩm sắp hết hàng</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Sản phẩm</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-300">Tồn kho
                                </th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-300">Tối thiểu
                                </th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">Hành động
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($lowStockProducts ?? [] as $product)
                                @php /** @var \App\Models\Product $product */ @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $product->sku }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-red-600">{{ $product->quantity }}</td>
                                    <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ $product->min_stock }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.products.show', $product) }}"
                                            class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Xem</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        Tuyệt vời! Không có sản phẩm nào sắp hết hàng.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.inventory-movements.index') }}"
                        class="text-lg font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                        Hoạt động gần đây
                    </a>
                </div>
                <div class="p-6 space-y-4">
                    @forelse ($inventoryMovements ?? [] as $movement)
                        <div class="flex space-x-3">
                            <div class="flex-shrink-0">
                                @if(optional($movement)->type == 'in')
                                    <span
                                        class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </span>
                                @else
                                    <span class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6">
                                            </path>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-800 dark:text-gray-200">
                                    @if(optional($movement)->type == 'in')
                                        <span class="font-medium">Nhập kho</span>
                                    @else
                                        <span class="font-medium">Xuất kho</span>
                                    @endif
                                    {{ optional($movement)->quantity_change ?? 0 }} ({{ optional(optional($movement)->product)->name ?? 'N/A' }})
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @if(optional(optional($movement)->created_at))
                                        @if(optional(optional($movement)->created_at)->gt(now()->subDay()))
                                            {{ optional(optional($movement)->created_at)->locale('vi')->diffForHumans() }}
                                        @else
                                            {{ optional(optional($movement)->created_at)->format('d/m/Y H:i') }}
                                        @endif
                                    @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Chưa có hoạt động nào gần đây.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
@endpush
