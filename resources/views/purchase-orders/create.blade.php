@extends('layouts.app')

@section('title', 'Tạo Phiếu Nhập Kho')

@section('content')
    <div class="p-6 max-w-6xl mx-auto" x-data="purchaseOrderForm({ suppliers: @js($suppliers), products: [] })"> {{-- Khởi
        tạo Alpine --}}
        <form method="POST" action="{{ route('admin.purchase-orders.store') }}">
            @csrf

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <p class="font-bold">Có lỗi xảy ra, vui lòng kiểm tra lại:</p>
                    <ul class="list-disc list-inside mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                {{-- Card Header --}}
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Tạo Phiếu Nhập Kho Mới</h2>
                        <a href="{{ route('admin.purchase-orders.index') }}"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 font-bold py-2 px-4 rounded-lg transition duration-300">
                            Quay lại
                        </a>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-6 space-y-6">
                    {{-- Thông tin chung --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nhà
                                cung cấp <span class="text-red-500">*</span></label>
                            <select id="supplier_id" name="supplier_id" required x-model="selectedSupplierId"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('supplier_id') border-red-500 @enderror">
                                <option value="">-- Chọn NCC --</option>
                                <template x-for="(name, id) in suppliers" :key="id">
                                    <option :value="id" x-text="name"></option>
                                </template>
                            </select>
                            @error('supplier_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="order_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ngày
                                đặt hàng <span class="text-red-500">*</span></label>
                            <input type="date" id="order_date" name="order_date"
                                value="{{ old('order_date', now()->format('Y-m-d')) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('order_date') border-red-500 @enderror">
                            @error('order_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="expected_date"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ngày dự kiến nhận</label>
                            <input type="date" id="expected_date" name="expected_date" value="{{ old('expected_date') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('expected_date') border-red-500 @enderror">
                            @error('expected_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Thêm Sản phẩm --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Chi tiết sản phẩm</h3>
                        {{-- Input tìm kiếm sản phẩm --}}
                        <div class="relative mb-4">
                            <label for="product-search" class="sr-only">Tìm sản phẩm</label>
                            <input type="text" id="product-search" placeholder="Tìm kiếm sản phẩm theo tên hoặc SKU..."
                                x-model="searchTerm" @input.debounce.300ms="searchProducts()"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-10"
                                autocomplete="off">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg x-show="isLoadingSearch" class="animate-spin h-5 w-5 text-gray-400"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                            {{-- Dropdown kết quả tìm kiếm --}}
                            <div x-show="searchResults.length > 0 && searchTerm.length > 1" @click.away="searchResults = []"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                style="display: none;"
                                class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 rounded-md shadow-lg max-h-60 overflow-auto border border-gray-200 dark:border-gray-600">
                                <ul>
                                    <template x-for="product in searchResults" :key="product.id">
                                        <li @click="addProduct(product)"
                                            class="cursor-pointer px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                            <span class="font-medium" x-text="product.name"></span> (<span
                                                x-text="product.sku"></span>) - Giá
                                            nhập: <span x-text="formatCurrency(product.price_buy)"></span>/<span
                                                x-text="product.unit"></span>
                                        </li>
                                    </template>
                                    <template
                                        x-if="searchResults.length === 0 && !isLoadingSearch && searchTerm.length > 1">
                                        <li class="px-4 py-2 text-sm text-gray-500">Không tìm thấy sản phẩm.</li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        {{-- Bảng danh sách sản phẩm đã thêm --}}
                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-md">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Sản
                                            phẩm</th>
                                        <th
                                            class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-300 w-24">
                                            Số lượng</th>
                                        <th
                                            class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300 w-40">
                                            Đơn giá (VNĐ)</th>
                                        <th
                                            class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300 w-40">
                                            Thành tiền (VNĐ)</th>
                                        <th
                                            class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-300 w-16">
                                            Xóa</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    {{-- Lặp qua các item đã thêm bằng Alpine --}}
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="px-4 py-2 align-top">
                                                <input type="hidden" :name="`items[${index}][product_id]`"
                                                    :value="item.product_id">
                                                <span class="font-medium text-gray-900 dark:text-gray-100"
                                                    x-text="item.product_name"></span>
                                                <span class="block text-xs text-gray-500 dark:text-gray-400"
                                                    x-text="`(SKU: ${item.sku})`"></span>
                                            </td>
                                            <td class="px-4 py-2 align-top">
                                                <input type="number" :name="`items[${index}][quantity]`"
                                                    x-model.number="item.quantity" min="1" required
                                                    class="w-full text-center rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </td>
                                            <td class="px-4 py-2 align-top">
                                                <input type="number" :name="`items[${index}][price]`"
                                                    x-model.number="item.price"
                                                    @input.debounce.150ms="item.price = formatPriceForInput($event.target.value)"
                                                    min="0" step="1000" required
                                                    class="w-full text-right rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </td>
                                            <td class="px-4 py-2 text-right align-top text-gray-900 dark:text-gray-100"
                                                x-text="formatCurrency(item.quantity * item.price)"></td>
                                            <td class="px-4 py-2 text-center align-top">
                                                <button type="button" @click="removeItem(index)" title="Xóa sản phẩm"
                                                    class="text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-100 dark:hover:bg-red-900/50">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                            clip-rule="evenodd"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    {{-- Hàng hiển thị khi chưa có item nào --}}
                                    <template x-if="items.length === 0">
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">Chưa
                                                có sản phẩm nào được
                                                thêm. Tìm kiếm và chọn sản phẩm ở trên.</td>
                                        </tr>
                                    </template>
                                </tbody>
                                {{-- Footer bảng: Tổng tiền --}}
                                <tfoot>
                                    <tr
                                        class="bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                                        <td colspan="3"
                                            class="px-4 py-2 text-right font-semibold text-gray-700 dark:text-gray-200">Tổng
                                            cộng:</td>
                                        <td class="px-4 py-2 text-right font-bold text-lg text-gray-900 dark:text-gray-100"
                                            x-text="formatCurrency(calculateTotal())"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            @error('items')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            @if ($errors->has('items.*')) {{-- Hiển thị lỗi chi tiết cho từng dòng item --}}
                                <div class="mt-2 text-sm text-red-600 space-y-1">
                                    @foreach ($errors->get('items.*') as $messages)
                                        @foreach ($messages as $message)
                                            <p>{{ $message }}</p>
                                        @endforeach
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Ghi chú --}}
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ghi
                            chú</label>
                        <textarea id="notes" name="notes" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div
                    class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 text-right space-x-3">
                    <a href="{{ route('admin.purchase-orders.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-300">Hủy</a>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">Lưu
                        Phiếu Nhập</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
@endpush
