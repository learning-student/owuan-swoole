<?php


namespace SwooleTW\Http\Tests;
use Orchestra\Testbench\TestCase;
use SwooleTW\Http\Cache\SwooleCacheProvider;
use SwooleTW\Http\LaravelServiceProvider;

class LaravelTestCase extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            LaravelServiceProvider::class,
            //SwooleCacheProvider::class
        ];
    }

}