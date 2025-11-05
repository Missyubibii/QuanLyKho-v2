export function poIndexPage(initialData) {
    console.log('poIndexPage init', initialData);
    return {
        search: initialData.search,
        statusFilter: initialData.statusFilter,
        supplierFilter: initialData.supplierFilter,
        dateFromFilter: initialData.dateFromFilter,
        dateToFilter: initialData.dateToFilter,
        allPoIds: initialData.allPoIds,
        selectedPOs: [],
        isLoading: false,
        // searchPoUrl: initialData.searchPoUrl,
        // poSearchTerm: '',
        // poSearchResults: [],
        // isLoadingPoSearch: false,

        get selectAll() { /* ... giống supplier ... */ return this.selectedPOs.length === this.allPoIds.length; },
        get selectAllIndeterminate() { /* ... giống supplier ... */ return this.selectedPOs.length > 0 && this.selectedPOs.length < this.allPoIds.length; },
        get showBulkActions() { return this.selectedPOs.length > 0; },

        searchPOs() { // Thay đổi tên hàm
            const params = new URLSearchParams();
            if (this.search) params.append('search', this.search);
            if (this.statusFilter) params.append('status', this.statusFilter);
            if (this.supplierFilter) params.append('supplier_id', this.supplierFilter);
            if (this.dateFromFilter) params.append('date_from', this.dateFromFilter);
            if (this.dateToFilter) params.append('date_to', this.dateToFilter);

            const queryString = params.toString();
            const currentUrl = new URL(window.location.href);
            const existingPage = currentUrl.searchParams.get('page');
            if (existingPage) params.append('page', existingPage);
            const newUrl = `${window.location.pathname}?${params.toString()}`;

            if (typeof Turbo !== 'undefined') { Turbo.visit(newUrl); } else { window.location.href = newUrl; }
        },

        toggleSelectAll() {
            if (this.selectAll) { this.selectedPOs = []; }
            else { this.selectedPOs = Array.from(this.allPoIds); }
        },
        togglePOSelection(poId) { // Thay đổi
            const index = this.selectedPOs.indexOf(poId);
            if (index > -1) { this.selectedPOs.splice(index, 1); }
            else { this.selectedPOs.push(poId); }
        },

        async bulkDeletePOs() {
            if (!confirm(`Bạn có chắc chắn muốn xóa ${this.selectedPOs.length} phiếu nhập đã chọn?`)) {
                return;
            }
            this.isLoading = true;
            try {
                const response = await fetch('/admin/purchase-orders/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ po_ids: this.selectedPOs })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: `Lỗi HTTP: ${response.status}` }));
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
                console.error('Lỗi khi xóa hàng loạt PO:', error.message);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: `Xóa thất bại: ${error.message}` }
                }));
            } finally {
                this.isLoading = false;
            }
        },

        async searchPO() {
            if (this.poSearchTerm.length < 2) {
                this.poSearchResults = [];
                return;
            }
            this.isLoadingPoSearch = true;
            try {
                // SỬ DỤNG `this.searchPoUrl` THAY VÌ CHUỖI CỨNG
                const response = await fetch(`${this.searchPoUrl}?term=${encodeURIComponent(this.poSearchTerm)}`);
                if (!response.ok) throw new Error('Network response was not ok');
                this.poSearchResults = await response.json();
            } catch (error) {
                console.error('Lỗi tìm kiếm PO:', error);
                this.poSearchResults = [];
            } finally {
                this.isLoadingPoSearch = false;
            }
        },

        selectPO(item) {
            // Khi chọn một kết quả, điều hướng đến trang chi tiết
            window.location.href = item.url;
        }
    }
}
