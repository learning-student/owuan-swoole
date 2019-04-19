<?php


namespace SwooleTW\Http\Cache;


use Laravel\Lumen\Routing\Router;

class SwooleLaravelRouter extends Router
{


    /**
     * overwrite lumen's route so we can mark routes as should be cached
     *
     * @param array|string $method
     * @param string $uri
     * @param mixed $action
     */
    public function addRoute($method, $uri, $action)
    {
        parent::addRoute($method, $uri, $action);

        // save only GET routes
        if ($method === 'GET' && is_array($action) && isset($action['cache'])) {

            // todo: remove static propery in future
            Caching::$cacheRoutes[] = $uri;
        }
    }


}
