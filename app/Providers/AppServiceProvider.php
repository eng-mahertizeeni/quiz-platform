<?php

namespace App\Providers;

use App\Models\GameSession;
use App\Policies\GameSessionPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(GameSession::class, GameSessionPolicy::class);
        Paginator::useBootstrapFive();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
