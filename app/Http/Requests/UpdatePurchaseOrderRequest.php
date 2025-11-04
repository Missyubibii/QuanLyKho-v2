<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Giống hệt Store
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ];
    }
    public function messages(): array
    {
        return [
            'items.required' => 'Phiếu nhập phải có ít nhất một sản phẩm.',
            'items.min' => 'Phiếu nhập phải có ít nhất một sản phẩm.',
            'items.*.product_id.required' => 'Vui lòng chọn sản phẩm cho dòng :position.',
            'items.*.product_id.exists' => 'Sản phẩm chọn ở dòng :position không hợp lệ.',
            'items.*.quantity.required' => 'Vui lòng nhập số lượng cho sản phẩm ở dòng :position.',
            'items.*.quantity.min' => 'Số lượng sản phẩm ở dòng :position phải lớn hơn 0.',
            'items.*.price.required' => 'Vui lòng nhập đơn giá cho sản phẩm ở dòng :position.',
            'items.*.price.min' => 'Đơn giá sản phẩm ở dòng :position phải lớn hơn hoặc bằng 0.',
        ];
    }

    // Tự động thêm :position vào thông báo lỗi cho mảng items
    protected function prepareForValidation()
    {
        if ($this->has('items')) {
            $items = $this->input('items');
            foreach ($items as $index => &$item) {
                $item['position'] = $index + 1;
            }
            $this->merge(['items' => $items]);
        }
    }
}
