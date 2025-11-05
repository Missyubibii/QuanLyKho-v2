export function supplierIndexPage(initialData)  {
    console.log('supplierIndexPage đã được khởi tạo!', initialData);
    return {
        searchTerm: '',
        searchResults: [],
        isLoadingSearch: false,

        // Hàm tìm kiếm nhà cung cấp bằng Fetch API
        async searchSuppliers() {
            if (this.searchTerm.length < 2) {
                this.searchResults = [];
                return;
            }
            this.isLoadingSearch = true;
            try {
                const response = await fetch(`/admin/suppliers/search-json?term=${encodeURIComponent(this.searchTerm)}`);
                if (!response.ok) throw new Error('Network response was not ok');
                this.searchResults = await response.json();
            } catch (error) {
                console.error('Lỗi tìm kiếm nhà cung cấp:', error);
                this.searchResults = [];
            } finally {
                this.isLoadingSearch = false;
            }
        },

        

        // Hàm chọn nhà cung cấp từ kết quả (ví dụ: để điền vào một form khác)
        selectSupplier(supplier) {
            // Bạn có thể dispatch một sự kiện tùy chỉnh để component khác lắng nghe
            window.dispatchEvent(new CustomEvent('supplier-selected', {
                detail: supplier
            }));
            this.searchTerm = ''; // Xóa ô tìm kiếm
            this.searchResults = []; // Ẩn dropdown
        },

                // Xóa hàng loạt
        async bulkDelete() {
            if (!confirm(`Bạn có chắc chắn muốn xóa ${this.selectedSuppliers.length} nhà cung cấp đã chọn?`)) {
                return;
            }
            this.isLoading = true;
            let response;
            try {
                response = await fetch('/admin/suppliers/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ supplier_ids: this.selectedSuppliers })
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
