export function salesOrderPage(initialData) {
    console.log('salesOrderPage init', initialData);
    return {
        search: initialData.search,
        statusFilter: initialData.statusFilter,
        customerFilter: initialData.customerFilter,
        dateFromFilter: initialData.dateFromFilter,
        dateToFilter: initialData.dateToFilter,
        allSoIds: initialData.allSoIds,
        selectedSOs: [],
        isLoading: false,

        get selectAll() { return this.selectedSOs.length === this.allSoIds.length; },
        get selectAllIndeterminate() { return this.selectedSOs.length > 0 && this.selectedSOs.length < this.allSoIds.length; },
        get showBulkActions() { return this.selectedSOs.length > 0; },

        searchSOs() {
            const params = new URLSearchParams();
            if (this.search) params.append('search', this.search);
            if (this.statusFilter) params.append('status', this.statusFilter);
            if (this.customerFilter) params.append('customer_id', this.customerFilter);
            if (this.dateFromFilter) params.append('date_from', this.dateFromFilter);
            if (this.dateToFilter) params.append('date_to', this.dateToFilter);

            const queryString = params.toString();
            const newUrl = `${window.location.pathname}?${queryString}`;

            if (typeof Turbo !== 'undefined') { Turbo.visit(newUrl); } else { window.location.href = newUrl; }
        },

        toggleSelectAll() {
            if (this.selectAll) { this.selectedSOs = []; }
            else { this.selectedSOs = Array.from(this.allSoIds); }
        },
        toggleSOSelection(soId) {
            const index = this.selectedSOs.indexOf(soId);
            if (index > -1) { this.selectedSOs.splice(index, 1); }
            else { this.selectedSOs.push(soId); }
        },

        async bulkDeleteSOs() {
            if (!confirm(`Bạn có chắc chắn muốn xóa ${this.selectedSOs.length} phiếu xuất đã chọn?`)) {
                return;
            }
            this.isLoading = true;
            try {
                const response = await fetch('/admin/sales-orders/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ so_ids: this.selectedSOs })
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
                console.error('Lỗi khi xóa hàng loạt SO:', error.message);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: `Xóa thất bại: ${error.message}` }
                }));
            } finally {
                this.isLoading = false;
            }
        }
    }
}
