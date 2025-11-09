<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'so_code', 'customer_id', 'user_id', 'order_date', 'expected_date', 'notes', 'status', 'total_amount'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->so_code)) {
                // Lấy một ID duy nhất từ bảng đếm
                $number = DB::table('sales_order_numbers')->insertGetId([]);

                // Tạo mã phiếu xuất
                $model->so_code = 'SO-' . now()->format('Ymd') . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function inventoryMovements()
    {
        return $this->morphMany(InventoryMovement::class, 'source');
    }
}
