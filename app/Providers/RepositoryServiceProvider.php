<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Laravel auto-resolves concrete classes automatically.
        // No manual bindings needed when using type-hinted constructors.
        // Add interface bindings here only if using Repository Interfaces pattern.
    }
}
