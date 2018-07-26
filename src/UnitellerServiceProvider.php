<?php

namespace Indigoram89\Laravel\Uniteller;

use Illuminate\Support\ServiceProvider;
use Indigoram89\Laravel\Uniteller\Contracts\Uniteller as UnitellerContract;

class UnitellerServiceProvider extends ServiceProvider
{
	/**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

	/**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/uniteller.php' => config_path('uniteller.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UnitellerContract::class, function ($app) {
            return new Uniteller($app);
        });

        $this->app->alias(UnitellerContract::class, 'uniteller');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['uniteller'];
    }
}
