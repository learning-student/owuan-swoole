<?php


namespace SwooleTW\Http\Cache;

use Anonym\Components\Cache\Cache;
use Anonym\Components\Cache\CacheInterface;
use Anonym\Components\Cache\DriverAdapterInterface;
use Anonym\Components\Cache\DriverNotInstalledException;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class Caching
 * @package SwooleTW\Http\Cache
 */
class Caching
{

    /**
     * @var array
     */
    public static $cacheRoutes = [];

    /**
     * @var  Cache
     */
    private $caching;


    /**
     * @var  array
     */
    private $config;

    /**
     * Caching constructor.
     * @param array $swooleCacheconfig
     * @param array $laravelCacheConfig
     */
    public function __construct(array $swooleCacheconfig)
    {
        $this->initializeCacheDriverWithConfigs($swooleCacheconfig);
    }


    /**
     * @param array $config
     * @throws DriverNotInstalledException
     */
    private function initializeCacheDriverWithConfigs(array $config): void
    {
        $this->config = $config;

        // get the selected default driver
        $driver = $config['driver'] ?? 'array';

        // get the driver configurations
        $driverConfig = $config['drivers'][$driver] ?? [];

        $this->caching = new Cache(
            $driverConfig,
            $driver
        );
    }


    /**
     * @param $request
     * @return bool
     */
    private function checkRequestShouldBeCached($request): bool
    {
        return mb_convert_case($request->server['request_method'], MB_CASE_LOWER) === "get";

    }

    /**
     * @param $request
     * @return bool
     */
    private function checkCacheExists($request): bool
    {
        if (!$this->checkRequestShouldBeCached($request)) {
            return false;
        }

        $uri = $request->server['request_uri'];


        return $this->caching->exists($uri);
    }


    /**
     * @param $request
     * @return Response
     */
    private function getCachedResponse($request): Response
    {
        $uri = $request->server['request_uri'];

        return $this->caching->get($uri);

    }

    /**
     * @return \Closure
     */
    public function cachePreEvent(): callable
    {
        return function ($request) {

            if (!$this->checkCacheExists($request)) {
                return false;
            }

            return $this->getCachedResponse($request);

        };
    }

    /**
     * @param $request
     * @return bool
     */
    private function checkAllCachedRoutes($request): bool
    {
        foreach (static::$cacheRoutes as $cacheRoute) {
            if ($request->is($cacheRoute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Closure
     */
    public function cachePostEvent(): callable
    {
        return function ($swooleRequest, $swooleResponse, $laravelRequest, Response $laravelResponse) {
            if (!$this->checkRequestShouldBeCached($swooleRequest) || !$this->checkAllCachedRoutes($laravelRequest)) {
                return false;
            }


            $this->caching->set(
                $swooleRequest->server['request_uri'],
                $laravelResponse,
                $this->config['lifespan'] ?? 3600
            );


            return true;
        };
    }
}