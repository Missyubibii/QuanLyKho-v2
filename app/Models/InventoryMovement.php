<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity_change',
        'quantity_after',
        'source_type',
        'source_id',
        'user_id',
        'notes'
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'quantity_after' => 'integer',
    ];

    /**
     * Get the product that owns the movement.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that performed the movement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the source model (PO or SO) for the movement.
     */
    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Lấy URL để xem chi tiết nguồn gốc của biến động.
     */
    public function getSourceUrlAttribute()
    {
        $source = $this->source;

        if (!$source) {
            return null;
        }

        // Kiểm tra loại của nguồn gốc để tạo route đúng
        if ($this->source_type === 'App\Models\SalesOrder') {
            return route("admin.sales-orders.show", $source->id);
        }

        if ($this->source_type === 'App\Models\PurchaseOrder') {
            return route("admin.purchase-orders.show", $source->id);
        }

        return null;
    }

    /**
     * Lấy mã nguồn gốc (PO/SO) để hiển thị.
     */
    public function getSourceCodeAttribute()
    {
        // Đảm bảo mối quan hệ 'source' đã được tải
        $source = $this->source;

        if (!$source) {
            return null;
        }

        if (method_exists($source, 'so_code')) {
            return 'SO-' . $source->so_code;
        }

        if (method_exists($source, 'po_code')) {
            return 'PO-' . $source->po_code;
        }

        return null;
    }

    /**
     * Lấy loại nguồn gốc (Nhập/Xuất) để hiển thị.
     */
    public function getSourceTypeDisplayAttribute()
    {
        // Lấy mối quan hệ 'source' đã được tải
        $source = $this->source;

        if (!$source) {
            return null;
        }

        // So sánh với tên class đầy đủ của nguồn gốc
        if ($this->source_type === 'App\Models\SalesOrder') {
            return 'Phiếu Xuất Kho';
        }

        if ($this->source_type === 'App\Models\PurchaseOrder') {
            return 'Phiếu Nhập Kho';
        }

        return 'Hệ thống';
    }
}
