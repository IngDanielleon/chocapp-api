<?php

namespace App\Providers;

use App\Events\IncidentCreated;
use App\Listeners\NotifyInsuranceOnIncident;
use App\Models\Document;
use App\Models\Incident;
use App\Models\Vehicle;
use App\Policies\DocumentPolicy;
use App\Policies\IncidentPolicy;
use App\Policies\VehiclePolicy;
use App\Repositories\Contracts\IncidentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\IncidentRepository;
use App\Repositories\UserRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IncidentRepositoryInterface::class, IncidentRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    public function boot(): void
    {
        // Rate limiters
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('heavy', function ($request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Policies
        Gate::policy(Incident::class, IncidentPolicy::class);
        Gate::policy(Vehicle::class,  VehiclePolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);

        // Events & Listeners
        Event::listen(IncidentCreated::class, NotifyInsuranceOnIncident::class);
    }
}
