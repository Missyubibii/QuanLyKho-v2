export function supplierIndexPage(initialData) {
    console.log('supplierIndexPage đã được khởi tạo!', initialData);
    return {
        // Dữ liệu ban đầu
        search: initialData.search,
        activeFilter: initialData.activeFilter, // Thay đổi: dùng activeFilter cho is_active
        allSupplierIds: initialData.allSupplierIds, // Thay đổi: allSupplierIds

        // Trạng thái
        selectedSuppliers: [], // Thay đổi: selectedSuppliers
        isLoading: false,

        // Computed: chọn tất cả
        get selectAll() {
            if (this.allSupplierIds.length === 0) return false;
            return this.selectedSuppliers.length === this.allSupplierIds.length;
        },

        // Computed: chọn một phần
        get selectAllIndeterminate() {
            return this.selectedSuppliers.length > 0 && this.selectedSuppliers.length < this.allSupplierIds.length;
        },

        // Computed: hiển thị thanh hành động
        get showBulkActions() {
            return this.selectedSuppliers.length > 0;
        },

        // Tìm kiếm/Lọc nhà cung cấp (Trigger tự động)
        searchSuppliers() {
            const params = new URLSearchParams();
            if (this.search) params.append('search', this.search);
            if (this.activeFilter !== '') params.append('is_active', this.activeFilter); // Thay đổi: is_active

            const queryString = params.toString();
            // Lấy URL hiện tại (bao gồm cả query string cũ nếu có phân trang)
            const currentUrl = new URL(window.location.href);
            // Giữ lại tham số 'page' nếu có, loại bỏ các tham số lọc cũ
            const existingPage = currentUrl.searchParams.get('page');
            if (existingPage) params.append('page', existingPage);

            const newUrl = `${window.location.pathname}?${params.toString()}`;

            // Sử dụng Turbo để load lại trang mà không full reload (nếu dùng Breeze)
            // Hoặc dùng window.location.href nếu không dùng Turbo
            if (typeof Turbo !== 'undefined') {
                Turbo.visit(newUrl);
            } else {
                window.location.href = newUrl;
            }
        },

        // Chọn/Bỏ chọn tất cả
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedSuppliers = [];
            } else {
                // Đảm bảo sao chép mảng đúng cách
                this.selectedSuppliers = Array.from(this.allSupplierIds);
            }
        },

        // Chọn/Bỏ chọn một nhà cung cấp
        toggleSupplierSelection(supplierId) {
            const index = this.selectedSuppliers.indexOf(supplierId);
            if (index > -1) {
                this.selectedSuppliers.splice(index, 1);
            } else {
                this.selectedSuppliers.push(supplierId);
            }
        },

        // Xóa hàng loạt
        async bulkDelete() {
            if (!confirm(`Bạn có chắc chắn muốn xóa ${this.selectedSuppliers.length} nhà cung cấp đã chọn?`)) { // Thay đổi message
                return;
            }
            this.isLoading = true;
            let response;
            try {
                response = await fetch('/admin/suppliers/bulk-delete', { // Thay đổi URL
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ supplier_ids: this.selectedSuppliers }) // Thay đổi key: supplier_ids
                });

                if (!response.ok) {
                    let errorData = { message: `Lỗi HTTP: ${response.status}` };
                    try { errorData = await response.json(); } catch (e) { }
                    throw new Error(errorData.message || `Lỗi không xác định (Status: ${response.status})`);
                }

                const result = await response.json();

                // Dispatch toast event
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'success', message: result.message }
                }));

                // Delay reload
                setTimeout(() => {
                    if (typeof Turbo !== 'undefined') { Turbo.visit(window.location.href); } else { window.location.reload(); }
                }, 1500);

            } catch (error) {
                console.error('Lỗi khi xóa hàng loạt nhà cung cấp:', error.message);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: `Xóa thất bại: ${error.message}` }
                }));
            } finally {
                this.isLoading = false;
            }
        }
    }
}
