<?php


namespace SwooleTW\Http\Cache;


use Anonym\Components\Cache\Cache;
use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use SwooleTW\Http\Server\Facades\Server;
use SwooleTW\Http\Server\Manager;
use Swoole\Http\Response;
use Swool\Http\Request;


class SwooleCacheProvider extends ServiceProvider
{


    public function register()
    {

        if (get_class($this->app) === "Laravel\Lumen\Application") {
            $this->app->configure('swoole_cache');
        }

        $config = $this->app->make('config')->get('swoole_cache');

        $caching = new Caching($config);


        $this->app->singleton(Caching::class, function () use ($caching) {
            return $caching;
        });

        /**
         * add caching pre-event
         *
         * @var Manager $swoole
         * @var Caching $caching
         */
        $swoole = $this->app->make(Manager::class);


        $this->app->singleton(Manager::class, function () use ($swoole, $caching) {

            $swoole->addPreEvent('request', $caching->cachePreEvent());
            $swoole->addPostEvent('request', $caching->cachePostEvent());

            return $swoole;
        });


    }


}