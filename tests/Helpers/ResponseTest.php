<?php


namespace SwooleTW\Http\Tests\Helpers;


use SwooleTW\Http\Tests\TestCase;
use \SwooleTW\Http\Helpers\Response;

/**
 * Class ResponseTest
 * @package SwooleTW\Http\Tests\Helpers
 */
class ResponseTest extends TestCase
{

    private $mock;

    protected function setUp()
    {
        $this->mock = \Mockery::mock('alias:SwooleTW\Http\Helpers\Response');


    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testResponseAddHeader()
    {

        $this->mock->shouldReceive('header')
            ->once()
            ->andReturn(true);

        $response = $this->mock->header('test: test');

        $this->assertTrue($response);
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */

    public function testResponseSetStatusCode()
    {

        $this->mock->shouldReceive('httpResponseCode')
            ->once()
            ->andReturn(true);

        $this->assertTrue(
            $this->mock->httpResponseCode(200)
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testReponseSetCookie()
    {

        $this->mock->shouldReceive('setCookie')
            ->once()
            ->andReturn(true);

        $this->assertTrue(
            $this->mock->setCookie('test', 'test')
        );
    }
}