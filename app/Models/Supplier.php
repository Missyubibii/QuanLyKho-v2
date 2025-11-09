<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_code',
        'is_active',
        'website',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Định nghĩa mối quan hệ với model Product.
     * Một nhà cung cấp có thể có nhiều sản phẩm.
     */


    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // Phương thức để lấy các nhà cung cấp đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
