@extends('layouts.app')

@section('title', 'Sửa Phiếu Xuất Kho: ' . $salesOrder->so_code)

@section('content')
<div class="p-6">
    <form method="POST" action="{{ route('admin.sales-orders.update', $salesOrder) }}" x-data="salesOrderForm({
        customers: @js($customers),
        initialItems: @js($salesOrder->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'product_name' => $item->product->name,
            'sku' => $item->product->sku,
            'quantity' => $item->quantity,
            'price' => $item->price,
            'unit' => $item->product->unit,
        ])),
        selectedCustomerId: '{{ old('customer_id', $salesOrder->customer_id) }}'
    })">
        @csrf
        @method('PUT')

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Sửa Phiếu Xuất: {{ $salesOrder->so_code }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Chỉnh sửa thông tin và chi tiết sản phẩm cho phiếu xuất kho.</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('admin.sales-orders.show', $salesOrder) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 font-bold py-2 px-4 rounded-lg transition duration-300">
                    Quay lại
                </a>
            </div>
        </div>

        {{-- Main Form Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
            {{-- General Information Section --}}
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Thông tin chung</h3>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Khách hàng <span class="text-red-500">*</span></label>
                        <select id="customer_id" name="customer_id" required x-model="selectedCustomerId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Chọn Khách Hàng --</option>
                            <template x-for="(name, id) in customers" :key="id">
                                <option :value="id" x-text="name" :selected="id == selectedCustomerId"></option>
                            </template>
                        </select>
                        @error('customer_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="order_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ngày đặt hàng <span class="text-red-500">*</span></label>
                        <input type="date" id="order_date" name="order_date" value="{{ old('order_date', $salesOrder->order_date->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('order_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="expected_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ngày dự kiến giao</label>
                        <input type="date" id="expected_date" name="expected_date" value="{{ old('expected_date', $salesOrder->expected_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('expected_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Product Details Section --}}
            <div class="border-t border-gray-200 dark:border-gray-700">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Chi tiết sản phẩm</h3>
                </div>
                <div class="p-6">
                    <div class="relative mb-4">
                        <input type="text" placeholder="Tìm kiếm và thêm sản phẩm..." x-model="searchTerm" @input.debounce.300ms="searchProducts()" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10" autocomplete="off">
                        <div x-show="searchResults.length > 0 && searchTerm.length > 1" @click.away="searchResults = []" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 rounded-md shadow-lg max-h-60 overflow-auto border border-gray-200 dark:border-gray-600">
                            <ul>
                                <template x-for="product in searchResults" :key="product.id">
                                    <li @click="addProduct(product)" class="cursor-pointer px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <div class="font-medium" x-text="product.name"></div>
                                        <div class="text-xs text-gray-500">SKU: <span x-text="product.sku"></span></div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Sản phẩm</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Số lượng</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Đơn giá</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Thành tiền</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Xóa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-6 py-4">
                                            <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                                            <div class="font-medium text-gray-900 dark:text-gray-100" x-text="item.product_name"></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">SKU: <span x-text="item.sku"></span></div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" required class="w-20 text-center rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <input type="number" :name="`items[${index}][price]`" x-model.number="item.price" min="0" step="1000" required class="w-32 text-right rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-gray-100" x-text="formatCurrency(item.quantity * item.price)"></td>
                                        <td class="px-6 py-4 text-center">
                                            <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-bold">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="items.length === 0">
                                    <tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">Chưa có sản phẩm nào. Hãy tìm kiếm và thêm sản phẩm vào phiếu.</td></tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 font-bold">
                                    <td colspan="3" class="px-6 py-4 text-right text-gray-700 dark:text-gray-200">Tổng cộng:</td>
                                    <td class="px-6 py-4 text-right text-lg text-gray-900 dark:text-gray-100" x-text="formatCurrency(calculateTotal())"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        @error('items')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Notes Section --}}
            <div class="border-t border-gray-200 dark:border-gray-700 p-6">
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ghi chú</label>
                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $salesOrder->notes) }}</textarea>
                </div>
            </div>

            {{-- Form Actions Footer --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                <a href="{{ route('admin.sales-orders.show', $salesOrder) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-300">Hủy</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">Cập nhật Phiếu Xuất</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script type="module">
        window.formatCurrency = function (value) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', minimumFractionDigits: 0 }).format(value);
        }
    </script>
@endpush
