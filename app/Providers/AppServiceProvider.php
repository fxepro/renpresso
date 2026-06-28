<?php
namespace App\Providers;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use App\Models\{Property, Lease, MaintenanceRequest};
use App\Policies\{PropertyPolicy, LeasePolicy, MaintenanceRequestPolicy};

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Trust Railway's load balancer proxy so HTTPS is detected correctly
        if (app()->environment('production')) {
            URL::forceScheme('https');
            \Illuminate\Http\Request::setTrustedProxies(
                ['REMOTE_ADDR'],
                \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
            );
        }

        Paginator::defaultView('vendor.pagination.dashboard');

        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Lease::class, LeasePolicy::class);
        Gate::policy(MaintenanceRequest::class, MaintenanceRequestPolicy::class);
    }
}
