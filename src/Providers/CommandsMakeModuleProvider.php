<?php

namespace ErikFig\Commands\MakeModule\Providers;

use ErikFig\Commands\MakeModule\Console\Commands\MakeModule;
use Illuminate\Support\ServiceProvider;

class CommandsMakeModuleProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModule::class,
            ]);
        }
    }
}
