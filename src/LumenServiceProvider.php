<?php

namespace SwooleTW\Http;

use SwooleTW\Http\Event\Event;
use SwooleTW\Http\Server\Manager;
use SwooleTW\Http\Middleware\AccessLog;
use SwooleTW\Http\Table\SwooleTable;

/**
 * @codeCoverageIgnore
 */
class LumenServiceProvider extends HttpServiceProvider
{

    /**
     *  register event manager
     */
    protected function registerEventManager(): void
    {
        $this->app->singleton(Event::class, Event::class);
        $this->app->alias(Event::class, 'swoole.event');

    }

    /**
     *
     */
    protected function registerSwooleTable(): void
    {
        $tables = $this->app->make('config')->get('swoole_http.tables', []);


        $this->app->singleton(SwooleTable::class, function () use($tables){
            return new SwooleTable($tables);
        });

        $this->app->alias(SwooleTable::class, 'swoole.table');
    }

    /**
     * Register manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton(Manager::class, function ($app) {
            return new Manager($app, 'lumen');
        });

        $this->app->alias(Manager::class, 'swoole.manager');
    }

    /**
     * Boot websocket routes.
     *
     * @return void
     */
    protected function bootWebsocketRoutes()
    {
        $app = $this->app;

        // router only exists after lumen 5.5
        if (property_exists($app, 'router')) {
            $app->router->group(['namespace' => 'SwooleTW\Http\Controllers'], function ($app) {
                require __DIR__ . '/../routes/lumen_routes.php';
            });
        } else {
            $app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
                require __DIR__ . '/../routes/lumen_routes.php';
            });
        }
    }

    /**
     * Register access log middleware to container.
     *
     * @return void
     */
    protected function pushAccessLogMiddleware()
    {
        $this->app->middleware(AccessLog::class);
    }
}
