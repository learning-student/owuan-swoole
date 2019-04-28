<?php


namespace SwooleTW\Http\Tests\Caching;


use Laravel\Lumen\Application;
use SwooleTW\Http\Cache\Caching;
use SwooleTW\Http\Cache\LaravelSwooleRouterProvider;
use SwooleTW\Http\Cache\SwooleCacheProvider;
use SwooleTW\Http\Cache\SwooleLaravelRouter;
use SwooleTW\Http\Cache\SwooleLumenRouter;
use SwooleTW\Http\LumenServiceProvider;
use SwooleTW\Http\Tests\LaravelTestCase;

class RouterTest extends LaravelTestCase
{


    protected function getPackageProviders($app)
    {
        return array_merge(
            parent::getPackageProviders($app),
            [
                SwooleCacheProvider::class
            ]
        ); 
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCacheHasBeenProvided()
    {
        $caching = $this->app->make('swoole.caching');

        $this->assertInstanceOf(Caching::class, $caching);
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLaravelSwooleRouterAddsToCache()
    {
        $this->app->singleton('router', function ($app){
           return new SwooleLaravelRouter($app);
        });

        $router = $this->app->make('router');

        $this->assertInstanceOf(SwooleLaravelRouter::class, $router);

        /**
         * @var SwooleLaravelRouter $router
         */

        $router->get('/', ['cache' => true, 'uses' => 'IndexController@index']);

        $caching = $this->app->make('swoole.caching');

        /**
         * @var Caching $caching
         */

        $routes = $caching->getCacheRoutes();

        $this->assertIsArray($routes);
        $this->assertContains("/", $routes);
    }

}