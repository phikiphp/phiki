<?php

namespace Phiki\Adapters\Laravel;

use Illuminate\Support\ServiceProvider;
use Phiki\Phiki;

class PhikiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Phiki::class, static fn () => new Phiki);    
    }
}
