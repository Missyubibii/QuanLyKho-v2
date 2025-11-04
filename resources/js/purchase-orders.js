export function poIndexPage(initialData) {
    console.log('poIndexPage init', initialData);
    return {
        search: initialData.search,
        statusFilter: initialData.statusFilter,
        supplierFilter: initialData.supplierFilter,
        dateFromFilter: initialData.dateFromFilter,
        dateToFilter: initialData.dateToFilter,
        allPoIds: initialData.allPoIds,
        selectedPOs: [], // Thay đổi
        isLoading: false,

        get selectAll() { /* ... giống supplier ... */ return this.selectedPOs.length === this.allPoIds.length; },
        get selectAllIndeterminate() { /* ... giống supplier ... */ return this.selectedPOs.length > 0 && this.selectedPOs.length < this.allPoIds.length; },
        get showBulkActions() { return this.selectedPOs.length > 0; },

        searchPOs() { // Thay đổi tên hàm
            const params = new URLSearchParams();
            if (this.search) params.append('search', this.search);
            if (this.statusFilter) params.append('status', this.statusFilter);
            if (this.supplierFilter) params.append('supplier_id', this.supplierFilter);
            // Thêm date filter nếu dùng
            // if (this.dateFromFilter) params.append('date_from', this.dateFromFilter);
            // if (this.dateToFilter) params.append('date_to', this.dateToFilter);

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
        // async bulkDelete() { // Cần tạo route và controller method cho bulk delete PO
        //      if (!confirm(`Xóa ${this.selectedPOs.length} phiếu nhập đã chọn?`)) return;
        //      this.isLoading = true;
        //      try {
        //          // ... fetch POST /admin/purchase-orders/bulk-delete ...
        //      } catch(e) { /* ... */ } finally { this.isLoading = false; }
        // }
    }
}
