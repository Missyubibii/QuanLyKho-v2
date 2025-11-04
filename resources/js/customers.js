export function customerIndexPage(initialData) {
    console.log('customerIndexPage đã được khởi tạo!', initialData);
    return {
        // Dữ liệu ban đầu
        search: initialData.search,
        activeFilter: initialData.activeFilter,
        allCustomerIds: initialData.allCustomerIds, // Thay đổi: allCustomerIds

        // Trạng thái
        selectedCustomers: [], // Thay đổi: selectedCustomers
        isLoading: false,

        // Computed: chọn tất cả
        get selectAll() {
            if (this.allCustomerIds.length === 0) return false;
            return this.selectedCustomers.length === this.allCustomerIds.length;
        },

        // Computed: chọn một phần
        get selectAllIndeterminate() {
            return this.selectedCustomers.length > 0 && this.selectedCustomers.length < this.allCustomerIds.length;
        },

        // Computed: hiển thị thanh hành động
        get showBulkActions() {
            return this.selectedCustomers.length > 0;
        },

        // Tìm kiếm/Lọc khách hàng (Trigger tự động)
        searchCustomers() { // Thay đổi tên hàm
            const params = new URLSearchParams();
            if (this.search) params.append('search', this.search);
            if (this.activeFilter !== '') params.append('is_active', this.activeFilter);

            const queryString = params.toString();
            const currentUrl = new URL(window.location.href);
            const existingPage = currentUrl.searchParams.get('page');
            if (existingPage) params.append('page', existingPage);

            const newUrl = `${window.location.pathname}?${params.toString()}`;

            if (typeof Turbo !== 'undefined') {
                Turbo.visit(newUrl);
            } else {
                window.location.href = newUrl;
            }
        },

        // Chọn/Bỏ chọn tất cả
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedCustomers = [];
            } else {
                this.selectedCustomers = Array.from(this.allCustomerIds);
            }
        },

        // Chọn/Bỏ chọn một khách hàng
        toggleCustomerSelection(customerId) { // Thay đổi tên hàm + tham số
            const index = this.selectedCustomers.indexOf(customerId);
            if (index > -1) {
                this.selectedCustomers.splice(index, 1);
            } else {
                this.selectedCustomers.push(customerId);
            }
        },

        // Xóa hàng loạt
        async bulkDelete() {
            if (!confirm(`Bạn có chắc chắn muốn xóa ${this.selectedCustomers.length} khách hàng đã chọn?`)) { // Thay đổi message
                return;
            }
            this.isLoading = true;
            let response;
            try {
                response = await fetch('/admin/customers/bulk-delete', { // Thay đổi URL
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ customer_ids: this.selectedCustomers }) // Thay đổi key: customer_ids
                });

                if (!response.ok) {
                    let errorData = { message: `Lỗi HTTP: ${response.status}` };
                    try { errorData = await response.json(); } catch (e) { }
                    throw new Error(errorData.message || `Lỗi không xác định (Status: ${response.status})`);
                }

                const result = await response.json();

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'success', message: result.message }
                }));

                setTimeout(() => {
                    if (typeof Turbo !== 'undefined') { Turbo.visit(window.location.href); } else { window.location.reload(); }
                }, 1500);

            } catch (error) {
                console.error('Lỗi khi xóa hàng loạt khách hàng:', error.message); // Thay đổi message log
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: `Xóa thất bại: ${error.message}` }
                }));
            } finally {
                this.isLoading = false;
            }
        }
    }
}
