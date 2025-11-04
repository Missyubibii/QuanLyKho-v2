<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'parent_id',
        'color',
        'icon'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Quan hệ cha-con
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Phương thức để lấy các danh mục đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Phương thức để lấy các danh mục gốc (không có cha)
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
