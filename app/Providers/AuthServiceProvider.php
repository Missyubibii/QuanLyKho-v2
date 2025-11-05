<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Product;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;

use App\Policies\ProductPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\SalesOrderPolicy;

use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
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
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        SalesOrder::class => SalesOrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // Bây giờ dòng này sẽ hoạt động
        $this->registerPolicies();

        Gate::before(function (User $user, string $ability) {
            Log::info('[AuthServiceProvider] Gate::before called', [
                'user_id' => $user->id,
                'ability' => $ability,
                'is_super_admin' => $user->hasRole('super_admin')
            ]);

            if ($user->hasRole('super_admin')) {
                Log::info('[AuthServiceProvider] AUTHORIZATION BYPASSED for super_admin.');
                return true;
            }

            Log::info('[AuthServiceProvider] Authorization NOT bypassed.');
            return null;
        });
    }
}
