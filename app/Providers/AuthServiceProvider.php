<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Product;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Customer;
use App\Policies\ProductPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\CustomerPolicy;
use Illuminate\Support\Facades\Log;

// class AuthServiceProvider extends ServiceProvider
class AuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Supplier::class => SupplierPolicy::class,
        Customer::class => CustomerPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->registerPolicies();

        // Định nghĩa các gate nếu cần
        Gate::before(function (User $user, $_ability) {
            // Thêm Log để kiểm tra xem hàm này có chạy đúng không
            \Log::info('[AuthServiceProvider] Checking Gate::before for user: ' . $user->id);

            // Logic kiểm tra vai trò 'super_admin'
            if ($user->roles()->where('name', 'super_admin')->exists()) {
                Log::info('[AuthServiceProvider] Gate::before -> Bypassed for super_admin (User ID: ' . $user->id . ')');
                return true;
            }

            Log::info('[AuthServiceProvider] Gate::before -> Did NOT bypass for user ID: ' . $user->id);
            return null;
        });
    }
}
