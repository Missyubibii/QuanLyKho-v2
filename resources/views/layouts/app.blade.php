<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"

    {{-- BỔ SUNG 2: Thêm data-turbo-permanent để chống nháy khi chuyển trang --}}
    data-turbo-permanent

    x-data="{
        expanded: localStorage.getItem('sidebarExpanded') !== 'false',
        darkMode: localStorage.getItem('darkMode') === 'true',
        toggleSidebar() {
            this.expanded = !this.expanded;
            localStorage.setItem('sidebarExpanded', this.expanded);
        },
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('darkMode', this.darkMode);
        }
    }"
    :class="{ 'dark': darkMode }"
    >
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quản Lý Kho</title>

    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen overflow-hidden font-sans antialiased bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
    <div class="flex h-full">
        <x-toast />

        {{-- Sidebar --}}
        @include('layouts.navigation')

        {{-- Main Content Area --}}
        <div class="flex flex-1 flex-col overflow-hidden transition-all duration-300 ease-in-out"
            :class="expanded ? 'ml-64' : 'ml-20'">

            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 z-10">
                <div class="flex justify-between items-center h-16 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button @click="toggleSidebar()"
                                class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                            @yield('title', 'Admin Dashboard')
                        </h1>
                    </div>

                    <div class="flex items-center space-x-4">
                        {{-- Nút chuyển Dark Mode --}}
                        <button @click="toggleDarkMode()"
                                class="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                            {{-- Icon Mặt Trời (Sáng) --}}
                            <svg x-show="!darkMode" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            {{-- Icon Mặt Trăng (Tối) --}}
                            <svg x-show="darkMode" style="display: none;" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        <div class="flex items-center justify-center" :class="expanded ? '' : 'justify-center'">
                            <x-notification-dropdown />
                        </div>

                        {{-- Dropdown User --}}
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    {{ Auth::user()->name }}
                                    <svg class="ml-2 -mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    Tài khoản
                                </x-dropdown-link>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                        Đăng xuất
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-900">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
