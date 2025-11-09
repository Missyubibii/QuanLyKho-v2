import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

import { productIndexPage } from './products.js';
import { supplierIndexPage } from './suppliers.js';
import { customerIndexPage } from './customers.js';
import { poIndexPage } from './purchase-orders.js';
import { purchaseOrderForm } from './purchase-order-form.js';
import { dynamicSearch } from './dynamic-search.js';
import { salesOrderPage} from './sales-orders.js';
import { salesOrderForm } from './sales-order-form.js';

// Đăng ký hàm với Alpine.js để có thể dùng trong Blade
window.productIndexPage = productIndexPage;
window.supplierIndexPage = supplierIndexPage;
window.customerIndexPage = customerIndexPage;
window.poIndexPage = poIndexPage;
window.purchaseOrderForm = purchaseOrderForm;
window.dynamicSearch = dynamicSearch;
window.salesOrderPage = salesOrderPage;
window.salesOrderForm = salesOrderForm;
window.Chart = Chart;

window.formatCurrency = function (value) {
    if (isNaN(value)) return '0 đ';
    // Sử dụng toLocaleString cho đơn giản hơn nếu không cần VND
    // return Number(value).toLocaleString('vi-VN') + ' đ';
    try { // Thêm try-catch để xử lý lỗi format nếu value không hợp lệ
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
    } catch (e) {
        console.error("Error formatting currency:", value, e);
        return 'Lỗi đ';
    }
}

window.Alpine = Alpine;

Alpine.start();
