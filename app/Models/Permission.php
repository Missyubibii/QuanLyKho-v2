<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_user');
    }

    public function roles()
    {
        // THÊM 'role_permission' LÀ THAM SỐ THỨ HAI
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
