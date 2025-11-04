<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Services\PermissionService; // Giữ lại vì bạn dùng để tạo user

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo roles
        $roles = [
            ['name' => 'super_admin', 'description' => 'Toàn quyền truy cập hệ thống'],
            ['name' => 'warehouse_manager', 'description' => 'Quản lý kho, sản phẩm, nhà cung cấp, báo cáo'],
            ['name' => 'warehouse_staff', 'description' => 'Thực hiện nhập/xuất hàng, xem báo cáo'],
            ['name' => 'viewer', 'description' => 'Chỉ xem dữ liệu'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }

        // Tạo permissions (Đã bổ sung các quyền còn thiếu)
        $permissions = [
            ['name' => 'products.view', 'description' => 'Xem sản phẩm'],
            ['name' => 'products.create', 'description' => 'Tạo sản phẩm'],
            ['name' => 'products.edit', 'description' => 'Chỉnh sửa sản phẩm'],
            ['name' => 'products.delete', 'description' => 'Xóa sản phẩm'],
            ['name' => 'suppliers.view', 'description' => 'Xem nhà cung cấp'],
            ['name' => 'suppliers.create', 'description' => 'Tạo nhà cung cấp'],
            ['name' => 'suppliers.edit', 'description' => 'Chỉnh sửa nhà cung cấp'],
            ['name' => 'suppliers.delete', 'description' => 'Xóa nhà cung cấp'],
            ['name' => 'customers.view', 'description' => 'Xem khách hàng'],
            ['name' => 'customers.create', 'description' => 'Tạo khách hàng'],
            ['name' => 'customers.edit', 'description' => 'Chỉnh sửa khách hàng'],
            ['name' => 'customers.delete', 'description' => 'Xóa khách hàng'],
            ['name' => 'purchase_orders.view', 'description' => 'Xem phiếu nhập kho'],
            ['name' => 'purchase_orders.create', 'description' => 'Tạo phiếu nhập kho'],
            ['name' => 'purchase_orders.edit', 'description' => 'Chỉnh sửa phiếu nhập kho'],
            ['name' => 'purchase_orders.delete', 'description' => 'Xóa phiếu nhập kho'],
            ['name' => 'purchase_orders.approve', 'description' => 'Duyệt phiếu nhập kho'],
            ['name' => 'sales_orders.view', 'description' => 'Xem phiếu xuất kho'],
            ['name' => 'sales_orders.create', 'description' => 'Tạo phiếu xuất kho'],
            ['name' => 'sales_orders.edit', 'description' => 'Chỉnh sửa phiếu xuất kho'],
            ['name' => 'sales_orders.delete', 'description' => 'Xóa phiếu xuất kho'],
            ['name' => 'sales_orders.approve', 'description' => 'Duyệt phiếu xuất kho'],
            ['name' => 'reports.view', 'description' => 'Xem báo cáo'],

            // --- BỔ SUNG CÁC QUYỀN CÒN THIẾU ---
            ['name' => 'users.view', 'description' => 'Xem người dùng'],
            ['name' => 'users.create', 'description' => 'Tạo người dùng'],
            ['name' => 'users.edit', 'description' => 'Sửa người dùng'],
            ['name' => 'users.delete', 'description' => 'Xóa người dùng'],

            ['name' => 'roles.view', 'description' => 'Xem vai trò & quyền'],
            ['name' => 'roles.edit', 'description' => 'Sửa vai trò & gán quyền'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }

        // --- TẠO USER MẪU ---
        $permissionService = new PermissionService();

        // 1. Super Admin
        $admin = User::firstOrCreate(['email' => 'admin@quanlykho.com'], [
            'name' => 'Super Admin',
            'password' => bcrypt('123123123'),
        ]);
        $permissionService->assignRole($admin, 'super_admin');

        // 2. Warehouse Manager
        $manager = User::firstOrCreate(['email' => 'manager@quanlykho.com'], [
            'name' => 'Quản Lý Kho',
            'password' => bcrypt('123123123'),
        ]);
        $permissionService->assignRole($manager, 'warehouse_manager');

        // 3. Warehouse Staff
        $staff = User::firstOrCreate(['email' => 'staff@quanlykho.com'], [
            'name' => 'Nhân Viên Kho',
            'password' => bcrypt('123123123'),
        ]);
        $permissionService->assignRole($staff, 'warehouse_staff');

        // 4. Viewer
        $viewerUser = User::firstOrCreate(['email' => 'viewer@quanlykho.com'], [
            'name' => 'Người Xem',
            'password' => bcrypt('123123123'),
        ]);
        $permissionService->assignRole($viewerUser, 'viewer');


        // --- GÁN QUYỀN CHI TIẾT CHO VAI TRÒ (ĐÃ CẬP NHẬT) ---

        $superAdmin = Role::where('name', 'super_admin')->first();
        $warehouseManager = Role::where('name', 'warehouse_manager')->first();
        $warehouseStaff = Role::where('name', 'warehouse_staff')->first();
        $viewer = Role::where('name', 'viewer')->first();

        // Super admin: Có tất cả quyền (bao gồm cả quyền 'users.*' và 'roles.*' mới)
        $superAdmin->permissions()->sync(Permission::pluck('id'));

        // Warehouse manager: Có các quyền quản lý kho, KHÔNG CÓ quyền xóa,
        // và KHÔNG có quyền quản lý người dùng/vai trò.
        $warehouseManager->permissions()->sync(
            Permission::where(function ($query) {
                // Lấy tất cả quyền trừ quyền quản lý user và role
                $query->where('name', 'not like', 'users.%')
                    ->where('name', 'not like', 'roles.%');
            })->pluck('id')
        );

        // Warehouse staff: Chỉ xem và tạo phiếu nhập/xuất
        $warehouseStaff->permissions()->sync(
            Permission::where(function ($query) {
                // Có tất cả quyền xem
                $query->where('name', 'like', '%.view')
                    // và quyền tạo phiếu nhập/xuất
                    ->orWhere('name', 'like', 'purchase_orders.create')
                    ->orWhere('name', 'like', 'sales_orders.create');
            })->pluck('id')
        );

        // Viewer: Chỉ có quyền xem
        $viewer->permissions()->sync(
            Permission::where('name', 'like', '%.view')->pluck('id')
        );
    }
}
