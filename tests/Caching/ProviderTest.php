<?php


namespace SwooleTW\Http\Tests\Caching;


use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use SwooleTW\Http\Cache\LaravelSwooleRouterProvider;
use SwooleTW\Http\Cache\SwooleLaravelRouter;
use SwooleTW\Http\LaravelServiceProvider;
use SwooleTW\Http\Tests\LaravelTestCase;

class ProviderTest extends LaravelTestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testProviderOverridesRouter()
    {

        $container = new Application();

        $provider = new LaravelSwooleRouterProvider($container);

        $provider->register();


        $router = $container->make('router');

        $this->assertInstanceOf(SwooleLaravelRouter::class, $router);
    }
}