<?php

/*
|--------------------------------------------------------------------------
| Swoole cache configurations.
|--------------------------------------------------------------------------
|
|  swoole cache works in a diffrent way than Laravel Caching system
|  but cache driver configs will be loaded from laravel cache config
|  here,you  will just specify that you'd rather use cache or not
|  normally, you requests will be handled by swoole redirected  to Laravel,
|  and then all laravel request lifecycle will triggered, but with caching laravel lifecycle
|  wont be triggered and cached response will be sent
|  @note: This package uses a lightweight alternative of Laravel/Cache
*/

return [


    /**
     *  if you provide enable, your requests will be cached
     *
     */
    'enabled' => false,


    /**
     *   this option controls that how long your response is kept in the cache
     *
     */
    'lifespan' => 3600 * 24,


    'driver' => 'array',


    /**
     *  you can store your drivers here,
     */
    'drivers' => [


        /**
         * driver configurations will be here
         *
         * for now only array cache supported
         */

        'memcache' => [

            // memcache host and port should be here

        ]
    ]

];