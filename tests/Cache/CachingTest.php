<?php


namespace SwooleTW\Http\Tests\Cache;

use PHPUnit\Framework\TestCase;
use SwooleTW\Http\Cache\Caching;

/**
 * Class CachingTest
 * @package SwooleTW\Http\Tests\Cache
 */
class CachingTest extends TestCase
{
    private $instance;

    /**
     *  initialize config
     */
    protected function setUp()
    {
        parent::setUp();


        $swooleCacheConfig = [

        ];

        $laravelConfigCache = [

        ];

        $this->instance = new Caching($swooleCacheConfig, $laravelConfigCache);
    }

    /**
     *  set value should pass
     */
    public function testSetValueArrayShouldPass() : void
    {

        $response = $this->instance->set('key', [
            'value' => 'test'
        ]);

        $this->assertTrue($response);
    }


}