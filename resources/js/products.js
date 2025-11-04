export function productIndexPage(initialData) {
    console.log('productIndexPage đã được khởi tạo!', initialData);
    return {
        // Dữ liệu ban đầu được truyền từ Laravel
        search: initialData.search,
        categoryFilter: initialData.categoryFilter,
        supplierFilter: initialData.supplierFilter,
        statusFilter: initialData.statusFilter,
        allProductIds: initialData.allProductIds,

        // Trạng thái của component
        selectedProducts: [],
        isLoading: false,

        // Computed property: Tự động tính toán trạng thái "chọn tất cả"
        get selectAll() {
            if (this.allProductIds.length === 0) return false;
            return this.selectedProducts.length === this.allProductIds.length;
        },

        // Computed property: Tự động tính toán trạng thái "chọn một phần"
        get selectAllIndeterminate() {
            return this.selectedProducts.length > 0 && this.selectedProducts.length < this.allProductIds.length;
        },

        get showBulkActions() {
            return this.selectedProducts.length > 0;
        },

        // Tìm kiếm sản phẩm
        searchProducts() {
            const params = new URLSearchParams();
            if (this.search) params.append('search', this.search);
            if (this.categoryFilter) params.append('category_id', this.categoryFilter);
            if (this.supplierFilter) params.append('supplier_id', this.supplierFilter);
            if (this.statusFilter) params.append('status', this.statusFilter);

            const queryString = params.toString();
            const newUrl = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;

            window.location.href = newUrl;
        },

        // Chọn/Bỏ chọn tất cả
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedProducts = [];
            } else {
                this.selectedProducts = [...this.allProductIds];
            }
        },

        // Chọn/Bỏ chọn một sản phẩm
        toggleProductSelection(productId) {
            const index = this.selectedProducts.indexOf(productId);
            if (index > -1) {
                this.selectedProducts.splice(index, 1);
            } else {
                this.selectedProducts.push(productId);
            }
        },

        // Xóa hàng loạt
        async bulkDelete() {
            if (!confirm(`Bạn có chắc chắn muốn xóa ${this.selectedProducts.length} sản phẩm đã chọn?`)) {
                return;
            }
            this.isLoading = true;
            try {
                const response = await fetch('/admin/products/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ product_ids: this.selectedProducts })
                });
                if (!response.ok) throw new Error('Xóa thất bại.');
                const result = await response.json();
                alert(result.message);
                window.location.reload();
            } catch (error) {
                console.error('Error:', error);
                alert('Đã có lỗi xảy ra. Vui lòng thử lại.');
            } finally {
                this.isLoading = false;
            }
        }
    }
}
