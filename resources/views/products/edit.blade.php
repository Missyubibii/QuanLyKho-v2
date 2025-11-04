{{-- resources/views/products/edit.blade.php --}}
@php
    $pageTitle = 'Chỉnh sửa sản phẩm';
@endphp

@extends('layouts.app')

@section('title', 'Chỉnh sửa thông tin sản phẩm')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">Cập nhật thông tin cho sản phẩm: <span
                    class="font-medium">{{ $product->name }}</span></p>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data"
                x-data="{
                            imagePreview: '{{ $product->image ? asset('storage/' . $product->image) : asset('images/placeholder.png') }}',
                            handleImageUpload(event) {
                                const file = event.target.files[0];
                                if (file) {
                                    this.imagePreview = URL.createObjectURL(file);
                                } else {
                                    this.imagePreview = '{{ $product->image ? asset('storage/' . $product->image) : asset('images/placeholder.png') }}';
                                }
                            }
                        }">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- ... (các trường Tên, SKU, Đơn vị giữ nguyên như file create) ... -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tên sản
                            phẩm <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mã SKU <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('sku')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Đơn vị
                            <span class="text-red-500">*</span></label>
                        <input type="text" id="unit" name="unit" value="{{ old('unit', $product->unit) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('unit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- ... (các dropdown Danh mục, Nhà cung cấp giữ nguyên) ... -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Danh
                            mục</label>
                        <select id="category_id" name="category_id"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}" {{ old('category_id', $product->category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nhà
                            cung cấp</label>
                        <select id="supplier_id" name="supplier_id"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- Chọn nhà cung cấp --</option>
                            @foreach($suppliers as $id => $name)
                                <option value="{{ $id }}" {{ old('supplier_id', $product->supplier_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- ... (trường Mô tả giữ nguyên) ... -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mô
                        tả</label>
                    <textarea id="description" name="description" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $product->description) }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Các trường về giá và số lượng -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- ... (các trường giá nhập, giá bán, tồn kho tối thiểu giữ nguyên) ... -->
                    <div>
                        <label for="price_buy" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Giá
                            nhập (VNĐ) <span class="text-red-500">*</span></label>
                        <input type="number" id="price_buy" name="price_buy"
                            value="{{ old('price_buy', $product->price_buy) }}" step="0.01" min="0" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('price_buy')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="price_sell" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Giá
                            bán (VNĐ) <span class="text-red-500">*</span></label>
                        <input type="number" id="price_sell" name="price_sell"
                            value="{{ old('price_sell', $product->price_sell) }}" step="0.01" min="0" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('price_sell')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- THAY ĐỔI QUAN TRỌNG: Ô Số lượng chỉ để xem -->
                    <div>
                        <label for="quantity_display"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Số lượng tồn kho</label>
                        <input type="text" id="quantity_display" value="{{ $product->quantity }} {{ $product->unit }}"
                            disabled
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 shadow-sm sm:text-sm cursor-not-allowed">
                        <p class="mt-1 text-xs text-gray-500">Số lượng chỉ thay đổi qua phiếu nhập/xuất kho.</p>
                    </div>

                    <div>
                        <label for="min_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tồn
                            kho tối thiểu <span class="text-red-500">*</span></label>
                        <input type="number" id="min_stock" name="min_stock"
                            value="{{ old('min_stock', $product->min_stock) }}" min="0" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('min_stock')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- ... (phần Trạng thái, Hình ảnh và các nút hành động giữ nguyên) ... -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Trạng
                            thái <span class="text-red-500">*</span></label>
                        <select id="status" name="status" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="in_stock" {{ old('status', $product->status) == 'in_stock' ? 'selected' : '' }}>Còn hàng</option>
                            <option value="out_of_stock" {{ old('status') == 'out_of_stock' ? 'selected' : '' }}>Hết hàng
                            </option>
                            <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Bảo trì
                            </option>
                        </select>
                        @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hình ảnh
                            sản phẩm</label>
                        <div class="mt-1 flex items-center space-x-4">
                            <img :src="imagePreview" alt="Product Preview"
                                class="h-20 w-20 object-cover rounded-md border border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <input type="file" id="image" name="image" accept="image/*"
                                    @change="handleImageUpload($event)"
                                    class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                                @error('image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <a href="{{ route('admin.products.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-300">Hủy
                        bỏ</a>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">Cập
                        nhật sản phẩm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
