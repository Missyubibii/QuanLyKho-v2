<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class PermissionService
{
    // Gán một quyền cho một vai trò
    public function assignPermission(string $roleName, string $permissionName)
    {
        $role = Role::where('name', $roleName)->first();
        $permission = Permission::where('name', $permissionName)->first();

        if ($role && $permission) {
            // Sử dụng syncWithoutDetaching để không xóa các quyền cũ
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }

    // Gán một vai trò cho một user
    public function assignRole(User $user, string $roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }
    }

    
}
