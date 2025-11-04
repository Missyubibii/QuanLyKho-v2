<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    // Tắt timestamps nếu không cần theo dõi created/updated cho từng item
    // public $timestamps = false;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor để tính subtotal tự động (tùy chọn)
    // protected static function boot() {
    //     parent::boot();
    //     static::saving(function ($item) {
    //         $item->subtotal = $item->quantity * $item->price;
    //     });
    // }
}
