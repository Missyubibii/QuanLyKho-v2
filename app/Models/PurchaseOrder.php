<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\HandlesAuthorization;
use App\Models\PurchaseOrderPolicy;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_code',
        'supplier_id',
        'user_id',
        'order_date',
        'expected_date',
        'status',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    // Tự động tạo mã PO khi tạo mới
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $latestOrder = static::withTrashed()->latest('id')->first();
            $nextId = $latestOrder ? $latestOrder->id + 1 : 1;
            $order->po_code = 'PO-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        });
    }

    public function supplier(): BelongsTo // Nhà cung cấp
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo // Người tạo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany // Các mặt hàng trong PO
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function inventoryMovements()
    {
        return $this->morphMany(InventoryMovement::class, 'source');
    }
}
