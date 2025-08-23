<?php

namespace Phiki\Adapters\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Phiki\Phiki;

class PhikiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Phiki::class, static fn () => (new Phiki)->cache(Cache::store()));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Blade::componentNamespace('Phiki\\Adapters\\Laravel\\Components', 'phiki');
    }
}
