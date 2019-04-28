<?php

namespace SwooleTW\Http;

use SwooleTW\Http\Event\Event;
use SwooleTW\Http\Server\Manager;
use Illuminate\Contracts\Http\Kernel;
use SwooleTW\Http\Middleware\AccessLog;
use SwooleTW\Http\Table\SwooleTable;

/**
 * @codeCoverageIgnore
 */
class LaravelServiceProvider extends HttpServiceProvider
{

    /**
     * register event manager
     */
    protected function registerEventManager() : void
    {
        $this->app->singleton(Event::class, Event::class);
        $this->app->alias(Event::class, 'swoole.event');

    }

    /**
     * Register manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton(Manager::class, function ($app) {
            return new Manager($app, 'laravel');
        });

        $this->app->alias(Manager::class, 'swoole.manager');

    }


    /**
     *
     */
    protected function registerSwooleTable() : void
    {

        $tables = $this->app->make('config')->get('swoole_http.tables', []);


        $this->app->singleton(SwooleTable::class, function () use($tables){
            return new SwooleTable($tables);
        });

        $this->app->alias(SwooleTable::class, 'swoole.table');
    }

    /**
     * Boot websocket routes.
     *
     * @return void
     */
    protected function bootWebsocketRoutes()
    {
        require __DIR__ . '/../routes/laravel_routes.php';
    }


    /**
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function pushAccessLogMiddleware()
    {
        $this->app->make(Kernel::class)->pushMiddleware(AccessLog::class);
    }
}
