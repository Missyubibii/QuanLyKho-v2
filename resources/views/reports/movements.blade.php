@extends('layouts.app')

@section('title', 'Báo cáo Nhập/Xuất')

@section('content')
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Báo cáo Nhập/Xuất</h1>
        </div>

        {{-- Biểu đồ [cite: 276] --}}
        <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Biến động 7 ngày qua</h3>
            <canvas id="movementChart"></canvas>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.reports.movements') }}" class="mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="mt-1 block rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="mt-1 block rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                <select name="type"
                    class="mt-1 block rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Tất cả loại</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Nhập kho</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Xuất kho</option>
                </select>

                <select name="product_id"
                    class="mt-1 block w-64 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Tất cả sản phẩm</option>
                    @foreach($products as $id => $name)
                        <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>

                <button type="submit"
                    class="mt-1 py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition duration-300">
                    Lọc
                </button>
                <a href="{{ route('admin.reports.movements') }}"
                    class="mt-1 py-2 px-4 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition duration-300">
                    Xóa lọc
                </a>
            </div>
        </form>

        {{-- Bảng Báo cáo --}}
        <div class="overflow-x-auto rounded-lg shadow-lg">
            <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Thời gian</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Sản phẩm</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Loại</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Biến động</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Tồn sau đó</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Nguồn</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Ghi chú</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($movements as $movement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-200">{{ $movement->product?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                @if($movement->type == 'in')
                                    <span class="font-medium text-green-600">Nhập kho</span>
                                @else
                                    <span class="font-medium text-red-600">Xuất kho</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-800 dark:text-gray-200">
                                {{ $movement->quantity_change }}</td>
                            <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">{{ $movement->quantity_after }}
                            </td>
                            <td class="px-6 py-4">
                                @if($movement->source_url)
                                    <a href="{{ $movement->source_url }}" class="text-indigo-600 hover:underline">
                                        {{ $movement->source_code }}
                                    </a>
                                @else
                                    <span>Hệ thống</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $movement->notes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-gray-500 dark:text-gray-400">Không tìm thấy biến động
                                nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $movements->links() }}
        </div>
    </div>
@endsection

{{-- Script để vẽ biểu đồ [cite: 276, 365, 473] --}}
@push('scripts')
    <script>
        // Đợi DOM load xong
        document.addEventListener('DOMContentLoaded', function () {
            // Lấy data từ API route chúng ta đã tạo
            fetch("{{ route('admin.reports.movement-summary') }}?days=7")
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('movementChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line', // Loại biểu đồ
                        data: {
                            labels: data.labels,
                            datasets: data.datasets
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            // Cấu hình dark mode cho chart
                            plugins: {
                                legend: {
                                    labels: {
                                        color: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#374151'
                                    }
                                }
                            }
                        }
                    });
                });
        });
    </script>
@endpush
