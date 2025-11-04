{{-- resources/views/components/notification-dropdown.blade.php --}}
<div x-data="{
    open: false,
    notifications: [],
    unreadCount: 0,
    loading: false,

    async fetchNotifications() {
        this.loading = true;
        try {
            const response = await fetch('{{ route("admin.notifications.index") }}');
            this.notifications = await response.json();
            this.unreadCount = this.notifications.filter(n => !n.is_read).length;
        } catch (error) {
            console.error('Lỗi khi tải thông báo:', error);
        } finally {
            this.loading = false;
        }
    },

    async markAsReadAndOpen() {
        this.open = !this.open;
        if (this.open && this.unreadCount > 0) {
            try {
                await fetch('{{ route("admin.notifications.read") }}', { method: 'POST' });
                // Cập nhật lại UI mà không cần fetch lại
                this.notifications.forEach(n => n.is_read = true);
                this.unreadCount = 0;
            } catch (error) {
                console.error('Lỗi khi đánh dấu đã đọc:', error);
            }
        }
    },

    init() {
        this.fetchNotifications();
        // Tải lại thông báo mỗi 30 giây
        setInterval(() => this.fetchNotifications(), 30000);
    }
}" class="relative">
    <!-- Nút chuông -->
    <button @click="markAsReadAndOpen()"
        class="relative p-2 rounded-lg dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
        <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path><path d="M4 2C2.8 3.7 2 5.7 2 8"></path><path d="M22 8c0-2.3-.8-4.3-2-6"></path></svg>
        <!-- Badge số lượng thông báo chưa đọc -->
        <span x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount" x-transition
            class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 rounded-full text-white text-xs flex items-center justify-center ring-2 ring-white dark:ring-gray-800">
        </span>
    </button>

    <!-- Dropdown nội dung thông báo -->
    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
        style="display: none;">

        <div class="p-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Thông báo</h3>
        </div>

        <div class="max-h-64 overflow-y-auto">
            <template x-if="loading">
                <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">Đang tải...</div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">Bạn không có thông báo nào.</div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <a href="#" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <!-- Icon theo loại thông báo -->
                            <svg x-show="notification.type === 'success'" class="h-5 w-5 text-green-400"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <svg x-show="notification.type === 'info'" class="h-5 w-5 text-blue-400" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <svg x-show="notification.type === 'warning'" class="h-5 w-5 text-yellow-400"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            <svg x-show="notification.type === 'error'" class="h-5 w-5 text-red-400" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="notification.title">
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="notification.message"></p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1"
                                x-text="new Date(notification.created_at).toLocaleString()"></p>
                        </div>
                        <div class="ml-2 flex-shrink-0">
                            <div x-show="!notification.is_read" class="h-2 w-2 bg-blue-600 rounded-full"></div>
                        </div>
                    </div>
                </a>
            </template>
        </div>
    </div>
</div>
