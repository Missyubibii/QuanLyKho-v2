<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Hệ Thống Quản Lý Kho</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Xóa bỏ các style inline cũ nếu Vite đang chạy --}}
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            {{-- Vite sẽ xử lý --}}
        @else
            {{-- Bạn có thể dán CSS fallback (nội dung thẻ <style> cũ) vào đây nếu cần --}}
        @endif
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

        <div class="flex flex-col items-center justify-center min-h-screen p-6">

            {{-- Thanh điều hướng trên cùng --}}
            <header class="w-full max-w-5xl mx-auto">
                <nav class="flex items-center justify-end gap-4 p-4">
                    @if (Route::has('login'))
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                            >
                                Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                            >
                                Đăng nhập
                            </a>

                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 transition"
                                >
                                    Đăng ký
                                </a>
                            @endif
                        @endauth
                    @endif
                </nav>
            </header>

            {{-- Nội dung chính (Hero Section) --}}
            <main class="flex-grow flex items-center justify-center w-full max-w-5xl mx-auto">
                <div class="text-center transition-opacity opacity-100 duration-750 starting:opacity-0">

                    {{-- Icon Ứng dụng --}}
                    <div class="flex justify-center mb-8">
                        <svg class="w-20 h-20 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                    </div>

                    {{-- Tiêu đề --}}
                    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-gray-100 mb-5">
                        Chào mừng đến với Hệ Thống Quản Lý Kho
                    </h1>

                    {{-- Mô tả --}}
                    <p class="text-lg lg:text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mb-10">
                        Giải pháp toàn diện để theo dõi, quản lý và báo cáo
                        tồn kho một cách hiệu quả và chính xác.
                    </p>

                    {{-- Nút Call to Action --}}
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                                class="inline-block w-full sm:w-auto px-10 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-all duration-300 text-lg">
                                Bắt đầu Đăng nhập
                            </a>
                        @endif
                    </div>

                </div>
            </main>

            {{-- Footer --}}
            <footer class="w-full max-w-5xl mx-auto p-4">
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    &copy; {{ date('Y') }} Hệ Thống Quản Lý Kho. Đã đăng ký Bản quyền.
                </p>
            </footer>
        </div>

    </body>
</html>
