/**
 * Component Alpine.js cho ô tìm kiếm động.
 * @param {Object} config - Cấu hình cho component.
 * @param {string} config.searchUrl - URL để fetch dữ liệu tìm kiếm.
 * @param {string} config.placeholder - Placeholder cho ô input.
 * @param {Function} config.onSelect - Hàm callback khi một mục được chọn.
 * @param {string} config.displayKey - Khóa trong object dữ liệu để hiển thị trong dropdown.
 * @param {string} [config.secondaryKey] - Khóa phụ để hiển thị thêm thông tin (tùy chọn).
 * @param {boolean} [config.allowNumericSearch=false] - Cho phép tìm kiếm theo số.
 */
export function dynamicSearch(config) {
    return {
        searchTerm: '',
        searchResults: [],
        isLoadingSearch: false,
        placeholder: config.placeholder || 'Tìm kiếm...',
        displayKey: config.displayKey || 'name',
        secondaryKey: config.secondaryKey || null,
        allowNumericSearch: config.allowNumericSearch || false, // Thêm tùy chọn mới

        async search() {
            if (this.searchTerm.length < 1) { // Cho phép tìm kiếm số với 1 ký tự
                this.searchResults = [];
                return;
            }
            this.isLoadingSearch = true;
            try {
                // Xây dựng URL với tham số numeric nếu cần
                let url = `${config.searchUrl}?term=${encodeURIComponent(this.searchTerm)}`;
                if (this.allowNumericSearch && !isNaN(this.searchTerm)) {
                    url += '&numeric=true';
                }

                const response = await fetch(url);
                if (!response.ok) throw new Error('Network response was not ok');
                this.searchResults = await response.json();
            } catch (error) {
                console.error('Lỗi tìm kiếm:', error);
                this.searchResults = [];
            } finally {
                this.isLoadingSearch = false;
            }
        },

        selectItem(item) {
            if (typeof config.onSelect === 'function') {
                config.onSelect(item);
            }
            this.searchTerm = '';
            this.searchResults = [];
        }
    }
}
