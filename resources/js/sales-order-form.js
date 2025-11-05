// resources/js/sales-order-form.js

export function salesOrderForm(config) {
    return {
        // Dữ liệu ban đầu
        customers: config.customers || {},
        items: config.initialItems || [], // Danh sách các item đã thêm
        selectedCustomerId: config.selectedCustomerId || '',
        searchTerm: '', // Từ khóa tìm kiếm SP
        searchResults: [], // Kết quả tìm kiếm SP
        isLoadingSearch: false,
        isSubmitting: false, // Trạng thái gửi form

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
                // Nếu sản phẩm đã tồn tại, tăng số lượng lên 1
                existingItem.quantity++;
            } else {
                // Nếu chưa, thêm mới
                this.items.push({
                    product_id: product.id,
                    product_name: product.name,
                    sku: product.sku,
                    unit: product.unit || 'cái',
                    quantity: 1,
                    // QUAN TRỌNG: Lấy giá bán cho phiếu xuất
                    price: product.price_sell || 0
                });
            }
            this.searchTerm = '';
            this.searchResults = [];
        },

        // Hàm xóa một item khỏi danh sách
        removeItem(index) {
            this.items.splice(index, 1);
        },

        // Hàm tính tổng tiền của phiếu xuất
        calculateTotal() {
            return this.items.reduce((total, item) => {
                const quantity = Number(item.quantity) || 0;
                const price = Number(item.price) || 0;
                return total + (quantity * price);
            }, 0);
        },

        // Hàm đảm bảo giá trị luôn là số nguyên, loại bỏ phần thập phân
        formatPriceForInput(price) {
            const numPrice = Number(price);
            if (isNaN(numPrice)) return 0;
            return Math.floor(numPrice);
        },

        // --- HÀM MỚI ĐỂ GỬI FORM BẰNG AJAX ---
        async submitForm(event) {
            if (this.isSubmitting) return;

            this.isSubmitting = true;
            const form = event.target;
            const formData = new FormData(form);

            try {
                // Lấy URL trực tiếp từ thuộc tính 'action' của form
                // Điều này đảm bảo chúng ta luôn gửi request đến đúng nơi
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json' // Thêm header này để báo cho server biết chúng ta mong đợi JSON
                    }
                });

                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const errorText = await response.text();
                    console.error("Server trả về HTML thay vì JSON. URL:", form.action, "Response:", errorText);
                    throw new Error('Server trả về phản hồi không mong đợi. Vui lòng kiểm tra console.');
                }

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.href = result.redirect_url;
                } else {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'error', message: result.message || 'Đã có lỗi xảy ra.' }
                    }));
                }
            } catch (error) {
                console.error('Lỗi khi gửi form:', error);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: error.message || 'Lỗi kết nối, vui lòng thử lại.' }
                }));
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}

// Hàm định dạng tiền tệ toàn cục
window.formatCurrency = function (value) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0
    }).format(value);
}
