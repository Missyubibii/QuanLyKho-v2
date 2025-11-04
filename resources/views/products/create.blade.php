{{-- resources/views/products/create.blade.php --}}
@php
    // Đặt tiêu đề trang
    $pageTitle = 'Thêm sản phẩm mới';
@endphp

@extends('layouts.app')

@section('title', 'Thêm sản phẩm mới')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Card chứa form -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <!-- Header của card -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Thông tin sản phẩm</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Điền đầy đủ thông tin để tạo một sản phẩm mới.
                </p>
            </div>

            <!-- Nội dung form -->
            <div class="p-6">
                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" x-data="{
                            imagePreview: '{{ asset('images/placeholder.png') }}',
                            handleImageUpload(event) {
                                const file = event.target.files[0];
                                if (file) {
                                    this.imagePreview = URL.createObjectURL(file);
                                } else {
                                    this.imagePreview = '{{ asset('images/placeholder.png') }}';
                                }
                            }
                        }">
                    @csrf

                    <!-- Các trường thông tin chung -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Tên sản phẩm -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tên sản
                                phẩm <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SKU -->
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mã SKU
                                <span class="text-red-500">*</span></label>
                            <input type="text" id="sku" name="sku" value="{{ old('sku') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('sku')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Đơn vị -->
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Đơn vị
                                <span class="text-red-500">*</span></label>
                            <input type="text" id="unit" name="unit" value="{{ old('unit', 'Cái') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('unit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Danh mục -->
                        <div>
                            <label for="category_id"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Danh mục</label>
                            <select id="category_id" name="category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nhà cung cấp -->
                        <div>
                            <label for="supplier_id"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nhà cung cấp</label>
                            <select id="supplier_id" name="supplier_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">-- Chọn nhà cung cấp --</option>
                                @foreach($suppliers as $id => $name)
                                    <option value="{{ $id }}" {{ old('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Mô tả -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mô
                            tả</label>
                        <textarea id="description" name="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Các trường về giá và số lượng -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div>
                            <label for="price_buy"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Giá nhập (VNĐ) <span
                                    class="text-red-500">*</span></label>
                            <input type="number" id="price_buy" name="price_buy" value="{{ old('price_buy') }}"
                                step="0.01" min="0" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('price_buy')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price_sell"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Giá bán (VNĐ) <span
                                    class="text-red-500">*</span></label>
                            <input type="number" id="price_sell" name="price_sell" value="{{ old('price_sell') }}"
                                step="0.01" min="0" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('price_sell')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Số
                                lượng tồn kho <span class="text-red-500">*</span></label>
                            <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 0) }}" min="0"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div> --}}

                        <div>
                            <label for="min_stock"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tồn kho tối thiểu
                                <span class="text-red-500">*</span></label>
                            <input type="number" id="min_stock" name="min_stock" value="{{ old('min_stock', 0) }}"
                                min="0" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('min_stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Trạng thái và Hình ảnh -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Trạng thái -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Trạng
                                thái <span class="text-red-500">*</span></label>
                            <select id="status" name="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="in_stock" {{ old('status', 'in_stock') == 'in_stock' ? 'selected' : '' }}>
                                    Còn hàng</option>
                                <option value="out_of_stock" {{ old('status') == 'out_of_stock' ? 'selected' : '' }}>Hết
                                    hàng</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Bảo trì
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Hình ảnh -->
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hình
                                ảnh sản phẩm</label>
                            <div class="mt-1 flex items-center space-x-4">
                                <img :src="imagePreview" alt="Product Preview"
                                    class="h-20 w-20 object-cover rounded-md border border-gray-300 dark:border-gray-600">
                                <div class="flex-1">
                                    <input type="file" id="image" name="image" accept="image/*"
                                        @change="handleImageUpload($event)"
                                        class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                                    @error('image')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Các nút hành động -->
                    <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <a href="{{ route('admin.products.index') }}"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-300">
                            Hủy bỏ
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                            <svg class="w-5 h-5 inline-block mr-2 -mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Lưu sản phẩm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
