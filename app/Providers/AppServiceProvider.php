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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        $this->forceRootUrlForAllowedHost();

        Paginator::defaultView('vendor.pagination.dashboard');

        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Lease::class, LeasePolicy::class);
        Gate::policy(MaintenanceRequest::class, MaintenanceRequestPolicy::class);
    }

    /** Use the incoming host when it is allowed (custom domain + Railway URL). */
    private function forceRootUrlForAllowedHost(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $request = request();
        if (! $request) {
            return;
        }

        $host = strtolower($request->getHost());
        if ($host === '') {
            return;
        }

        $allowed = $this->allowedAppHosts();

        if (! in_array($host, $allowed, true)) {
            return;
        }

        URL::forceRootUrl('https://'.$host);
    }

    /** @return list<string> */
    private function allowedAppHosts(): array
    {
        $fromEnv = array_filter(array_map(
            static fn (string $h): string => strtolower(trim($h)),
            explode(',', (string) env('APP_ALLOWED_HOSTS', ''))
        ));

        if ($fromEnv !== []) {
            return array_values(array_unique($fromEnv));
        }

        $hosts = [];
        foreach ([config('app.url'), env('APP_RAILWAY_URL')] as $url) {
            if (! is_string($url) || $url === '') {
                continue;
            }
            $parsed = parse_url($url, PHP_URL_HOST);
            if (is_string($parsed) && $parsed !== '') {
                $hosts[] = strtolower($parsed);
            }
        }

        return array_values(array_unique($hosts));
    }
}
