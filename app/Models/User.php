<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Mối quan hệ nhiều-nhiều với Role.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Quan hệ nhiều-nhiều với Permission (gán quyền trực tiếp).
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    /**
     * Kiểm tra xem người dùng có quyền hay không (bao gồm cả quyền trực tiếp và quyền qua vai trò).
     */
    // public function hasPermission(string $permissionName): bool
    // {
    //     // 1. Kiểm tra quyền trực tiếp gán cho user
    //     if ($this->permissions()->where('name', $permissionName)->exists()) {
    //         return true;
    //     }

    //     // 2. Nếu không có quyền trực tiếp, kiểm tra quyền thông qua các vai trò
    //     foreach ($this->roles as $role) {
    //         if ($role->permissions()->where('name', $permissionName)->exists()) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    public function hasPermission($permission)
    {
        // Lấy tất cả các tên quyền của user thông qua các vai trò của họ
        $permissions = $this->loadMissing('roles.permissions')
                        ->roles->pluck('permissions')
                        ->flatten()
                        ->pluck('name')
                        ->unique()
                        ->toArray();

        // Kiểm tra xem quyền cần tìm có nằm trong danh sách không
        return in_array($permission, $permissions);
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
