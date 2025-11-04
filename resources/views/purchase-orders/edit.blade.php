@extends('layouts.app')

@section('title', 'Sửa Phiếu Nhập Kho: ' . $purchaseOrder->po_code)

@section('content')
<div class="p-6 max-w-6xl mx-auto" x-data="purchaseOrderForm({
        suppliers: @js($suppliers),
        initialItems: @js($purchaseOrder->items->map(fn($item) => [ // Chuyển đổi items sang định dạng JS cần
            'product_id' => $item->product_id,
            'product_name' => $item->product->name,
            'sku' => $item->product->sku,
            'quantity' => $item->quantity,
            'price' => $item->price,
            'unit' => $item->product->unit,
        ])),
        selectedSupplierId: '{{ old('supplier_id', $purchaseOrder->supplier_id) }}' // Pre-fill supplier
     })">
    <form method="POST" action="{{ route('admin.purchase-orders.update', $purchaseOrder) }}">
        @csrf
        @method('PUT')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg">
            {{-- Card Header --}}
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Sửa Phiếu Nhập:
                        {{ $purchaseOrder->po_code }}</h2>
                    <a href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}" class="bg-gray-200 ...">Quay
                        lại</a>
                </div>
            </div>

            {{-- Card Body (Gần giống Create, chỉ khác value) --}}
            <div class="p-6 space-y-6">
                {{-- Thông tin chung --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="supplier_id" class="block ...">Nhà cung cấp <span
                                class="text-red-500">*</span></label>
                        <select id="supplier_id" name="supplier_id" required x-model="selectedSupplierId"
                            class="mt-1 ...">
                            <option value="">-- Chọn NCC --</option>
                            <template x-for="(name, id) in suppliers" :key="id">
                                {{-- Dùng :selected để chọn giá trị ban đầu --}}
                                <option :value="id" x-text="name" :selected="id == selectedSupplierId"></option>
                            </template>
                        </select>
                        @error('supplier_id')<p class="mt-1 ...">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="order_date" class="block ...">Ngày đặt hàng <span
                                class="text-red-500">*</span></label>
                        <input type="date" id="order_date" name="order_date"
                            value="{{ old('order_date', $purchaseOrder->order_date->format('Y-m-d')) }}" required
                            class="mt-1 ...">
                        @error('order_date')<p class="mt-1 ...">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="expected_date" class="block ...">Ngày dự kiến nhận</label>
                        <input type="date" id="expected_date" name="expected_date"
                            value="{{ old('expected_date', $purchaseOrder->expected_date?->format('Y-m-d')) }}"
                            class="mt-1 ...">
                        @error('expected_date')<p class="mt-1 ...">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Thêm Sản phẩm (Giống hệt Create) --}}
                <div class="border-t ... pt-6">
                    <h3 class="text-lg ... mb-4">Chi tiết sản phẩm</h3>
                    <div class="relative mb-4">
                        <input type="text" placeholder="Tìm kiếm sản phẩm..." x-model="searchTerm"
                            @input.debounce.300ms="searchProducts()" class="w-full ...">
                        <div x-show="searchResults.length > 0 && searchTerm.length > 1" @click.away="searchResults = []"
                            class="absolute z-10 ...">
                            <ul><template x-for="product in searchResults" :key="product.id">
                                    <li @click="addProduct(product)" class="cursor-pointer ..."><span
                                            x-text="product.name"></span> ...</li>
                                </template></ul>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full ...">
                            <thead>
                                <tr>
                                    <th ...>Sản phẩm</th>
                                    <th ...>Số lượng</th>
                                    <th ...>Đơn giá</th>
                                    <th ...>Thành tiền</th>
                                    <th ...>Xóa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="border-b ...">
                                        <td><input type="hidden" :name="`items[${index}][product_id]`"
                                                :value="item.product_id"><span x-text="item.product_name"></span>...
                                        </td>
                                        <td><input type="number" :name="`items[${index}][quantity]`"
                                                x-model.number="item.quantity" min="1" required class="w-20 ..."></td>
                                        <td><input type="number" :name="`items[${index}][price]`"
                                                x-model.number="item.price" min="0" step="1000" required
                                                class="w-32 ..."></td>
                                        <td x-text="formatCurrency(item.quantity * item.price)"></td>
                                        <td><button type="button" @click="removeItem(index)"
                                                class="text-red-500 ...">&times;</button></td>
                                    </tr>
                                </template>
                                <template x-if="items.length === 0">
                                    <tr>
                                        <td colspan="5" class="text-center ...">Chưa có sản phẩm.</td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right ...">Tổng cộng:</td>
                                    <td class="text-right ..." x-text="formatCurrency(calculateTotal())"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        @error('items')<p class="mt-2 ...">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Ghi chú --}}
                <div>
                    <label for="notes" class="block ...">Ghi chú</label>
                    <textarea id="notes" name="notes" rows="3"
                        class="mt-1 ...">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="px-6 py-4 bg-gray-50 ... text-right space-x-3">
                <a href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}" class="bg-gray-300 ...">Hủy</a>
                <button type="submit" class="bg-indigo-600 ...">Cập nhật Phiếu Nhập</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    {{-- Import JS cho form PO --}}
    <script type="module">
        window.formatCurrency = function (value) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND',
                minimumFractionDigits: 0
            }).format(value);
        }
    </script>
@endpush
