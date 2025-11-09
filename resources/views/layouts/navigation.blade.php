{{-- resources/views/layouts/navigation.blade.php --}}
<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-lg transition-all duration-300 ease-in-out"
    :class="expanded ? 'w-64' : 'w-20'">

    <!-- Logo -->
    <div class="flex items-center justify-center h-16 shrink-0 border-b border-gray-200 dark:border-gray-700">
        <a href="{{ route('dashboard') }}" class="flex items-center">
            <span class="text-2xl text-indigo-600 dark:text-indigo-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z">
                    </path>
                    <path d="M6 18h12"></path>
                    <path d="M6 14h12"></path>
                    <rect width="12" height="12" x="6" y="10"></rect>
                </svg>
            </span>
            <span class="ml-3 text-xl font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap"
                x-show="expanded" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                KhoAdmin
            </span>
        </a>
    </div>

    <!-- Navigation -->
    @php
        $menu = [
            [
                'name' => 'Trang chính',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>',
                'route' => 'dashboard'
            ],
            [
                'name' => 'Quản lý sản phẩm',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>',
                'route' => 'admin.products.index'
            ],
            [
                'name' => 'Quản lý nhà cung cấp',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 13a3 3 0 1 0-6 0"></path><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"></path><circle cx="12" cy="8" r="2"></circle></svg>',
                'route' => 'admin.suppliers.index'
            ],
            [
                'name' => 'Quản lý khách hàng',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
                'route' => 'admin.customers.index'
            ],
            [
                'name' => 'Quản lý Nhập kho',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"></rect><path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"></path><path d="M16 4h2a2 2 0 0 1 2 2v4"></path><path d="M21 14H11"></path><path d="m15 10-4 4 4 4"></path></svg>',
                'route' => 'admin.purchase-orders.index'
            ],

            [
                'name' => 'Quản lý Xuất kho',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H9a1 1 0 0 0-1 1v2c0 .6.4 1 1 1h6c.6 0 1-.4 1-1V3c0-.6-.4-1-1-1Z"></path><path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2M16 4h2a2 2 0 0 1 2 2v2M11 14h10"></path><path d="m17 10 4 4-4 4"></path></svg>',
                'route' => 'admin.sales-orders.index'
            ],

            [
                'name' => 'Lịch sử Tồn kho',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>',
                'route' => 'admin.inventory-movements.index'
            ],

            [
                'name' => 'Báo cáo Tồn kho',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v16a2 2 0 0 0 2 2h16"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>',
                'route' => 'admin.reports.inventory'
            ],

            [
                'name' => 'Báo cáo Nhập/Xuất',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v16a2 2 0 0 0 2 2h16"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>',
                'route' => 'admin.reports.movements'
            ]

        ];
    @endphp

    <nav class="flex-1 overflow-y-auto mt-4 px-2 space-y-1">
        @foreach ($menu as $item)
            <a href="{{ route($item['route']) }}" class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-md
                    {{ request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*')
            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200'
            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white' }}
                        transition-colors duration-150" :class="expanded ? '' : 'justify-center'"
                :title="expanded ? '' : '{{ $item['name'] }}'">
                <div class="flex-shrink-0">{!! $item['icon'] !!}</div>
                <span x-show="expanded" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-2">
                    {{ $item['name'] }}
                </span>
            </a>
        @endforeach
    </nav>


</aside>
