export function purchaseOrderForm(config) {
    return {
        suppliers: config.suppliers || {},
        items: config.initialItems || [], // Danh sách các item đã thêm
        selectedSupplierId: config.selectedSupplierId || '',
        searchTerm: '', // Từ khóa tìm kiếm SP
        searchResults: [], // Kết quả tìm kiếm SP
        isLoadingSearch: false,

        // Hàm tìm kiếm sản phẩm bằng Fetch API
        async searchProducts() {
            if (this.searchTerm.length < 2) {
                this.searchResults = [];
                return;
            }
            this.isLoadingSearch = true;
            try {
                const response = await fetch(`/admin/products/search-json?term=${encodeURIComponent(this.searchTerm)}`);
                if (!response.ok) throw new Error('Network response was not ok');
                this.searchResults = await response.json();
            } catch (error) {
                console.error('Lỗi tìm kiếm sản phẩm:', error);
                this.searchResults = [];
            } finally {
                this.isLoadingSearch = false;
            }
        },

        // Hàm thêm sản phẩm vào danh sách items
        addProduct(product) {
            const existingItem = this.items.find(item => item.product_id === product.id);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                this.items.push({
                    product_id: product.id,
                    product_name: product.name,
                    sku: product.sku,
                    unit: product.unit || 'cái',
                    quantity: 1,
                    price: product.price_buy || 0 // Giá nhập mặc định
                });
            }
            this.searchTerm = '';
            this.searchResults = [];
        },

        // Hàm xóa một item khỏi danh sách
        removeItem(index) {
            this.items.splice(index, 1);
        },

        // Hàm tính tổng tiền của phiếu nhập
        calculateTotal() {
            return this.items.reduce((total, item) => {
                const quantity = Number(item.quantity) || 0;
                const price = Number(item.price) || 0;
                return total + (quantity * price);
            }, 0);
        },

        // --- HÀM MỚI ĐỂ XỬ LÝ GIÁ ---
        // Hàm này đảm bảo giá trị luôn là số nguyên, loại bỏ phần thập phân
        formatPriceForInput(price) {
            const numPrice = Number(price);
            if (isNaN(numPrice)) return 0;
            // Làm tròn xuống để loại bỏ phần thập phân
            return Math.floor(numPrice);
        }
    }
}
