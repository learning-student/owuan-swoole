<?php


namespace SwooleTW\Http\Cache;


use Illuminate\Support\ServiceProvider;

/**
 * Class LaravelSwooleRouterProvider
 * @package SwooleTW\Http\Cache
 */
class LaravelSwooleRouterProvider extends ServiceProvider
{

    /**
     *  register SwooleLaravelRouter
     *
     */
    public function register()
    {
        // overwrite laravel built-in router
        $this->app->singleton('router', function ($app) {
            return new SwooleLaravelRouter($app);
        });
    }
}