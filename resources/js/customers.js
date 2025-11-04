// resources/js/customers.js

export function customerIndexPage(initialData) {
    return {
        search: initialData.search,
        activeFilter: initialData.activeFilter,
        allCustomerIds: initialData.allCustomerIds,
        selectedCustomers: [],
        isLoading: false,

        get selectAll() {
            if (this.allCustomerIds.length === 0) return false;
            return this.selectedCustomers.length === this.allCustomerIds.length;
        },

        get selectAllIndeterminate() {
            return this.selectedCustomers.length > 0 && this.selectedCustomers.length < this.allCustomerIds.length;
        },

        get showBulkActions() {
            return this.selectedCustomers.length > 0;
        },

        applyFilters() {
            const params = new URLSearchParams();
            if (this.search) params.append('search', this.search);
            if (this.activeFilter !== '') params.append('is_active', this.activeFilter);

            const queryString = params.toString();
            const newUrl = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;

            if (typeof Turbo !== 'undefined') {
                Turbo.visit(newUrl);
            } else {
                window.location.href = newUrl;
            }
        },

        // === SỬA LẠI HÀM toggleSelectAll ===
        toggleSelectAll() {
            if (this.selectAll) {
                // Nếu tất cả đã được chọn, bỏ chọn tất cả
                this.selectedCustomers = [];
            } else {
                // Nếu chưa, chọn tất cả
                // Sử dụng Array.from để tạo một bản sao mới, tránh các vấn đề về tham chiếu
                this.selectedCustomers = Array.from(this.allCustomerIds);
            }
        },
        // === KẾT THÚC SỬA ===

        toggleCustomerSelection(customerId) {
            const index = this.selectedCustomers.indexOf(customerId);
            if (index > -1) {
                this.selectedCustomers.splice(index, 1);
            } else {
                this.selectedCustomers.push(customerId);
            }
        },

        async bulkDelete() {
            if (!confirm(`Bạn có chắc chắn muốn xóa ${this.selectedCustomers.length} khách hàng đã chọn?`)) {
                return;
            }
            this.isLoading = true;
            try {
                const response = await fetch('/admin/customers/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ customer_ids: this.selectedCustomers })
                });

                if (!response.ok) {
                    // Lấy thông báo lỗi từ server nếu có
                    const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định.' }));
                    throw new Error(errorData.message || `Lỗi HTTP: ${response.status}`);
                }

                const result = await response.json();

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'success', message: result.message }
                }));

                // Tải lại trang sau một khoảng thời gian ngắn để người dùng thấy thông báo
                setTimeout(() => {
                    if (typeof Turbo !== 'undefined') {
                        Turbo.visit(window.location.href);
                    } else {
                        window.location.reload();
                    }
                }, 1500);

            } catch (error) {
                console.error('Lỗi khi xóa hàng loạt khách hàng:', error.message);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: `Xóa thất bại: ${error.message}` }
                }));
            } finally {
                this.isLoading = false;
            }
        }
    }
}
