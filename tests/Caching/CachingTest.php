<?php


namespace SwooleTW\Http\Tests\Caching;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use SwooleTW\Http\Cache\Caching;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

class CachingTest extends TestCase
{

    private $mockRequest;


    private $mockResponse;
    /**
     * @var Caching
     */
    private $caching;

    protected function setUp() : void
    {
        $this->mockResponse = new SwooleResponse();

        $this->caching = new Caching([
            'driver' => 'array'
        ]);


        $this->mockRequest = new SwooleRequest();

        $this->mockRequest->server = [
            'request_method' => 'get',
            'request_uri' => '/'
        ];

    }


    public function testCachePreEventsShouldReturnFalseWhenMethodNotGet()
    {
        $this->mockRequest->server = [
            'request_method' => 'post',
            'request_uri' => '/'
        ];

        $preEvent = $this->caching->cachePreEvent();


        $response = $preEvent($this->mockRequest);

        $this->assertFalse($response);
    }

    public function testCachePreEventsShouldReturnFalseWhenRouteNotCached()
    {
        $preEvent = $this->caching->cachePreEvent();


        $response = $preEvent($this->mockRequest);

        $this->assertFalse($response);
    }

    /**
     *
     */
    public function testCachePreEventsShouldReturnFalseWhenCacheNotExists()
    {
        $this->caching->cacheRoute('/');


        $preEvent = $this->caching->cachePreEvent();


        $response = $preEvent($this->mockRequest);

        $this->assertFalse($response);
    }

    public function testCachePreEventShouldReturnContentWhenCacheExists()
    {

        $this->caching->cacheRoute('/');


        $this->caching->getCaching()->set(
            '/',
            new Response()
        );

        $preEvent = $this->caching->cachePreEvent();


        $response = $preEvent($this->mockRequest);

        $this->assertInstanceOf(Response::class,  $response);
    }

    private function getLaravelRequestMocks()
    {
        $laravelRequestMock = \Mockery::mock(\Laravel\Lumen\Http\Request::class);



        $laravelRequestMock->shouldReceive('is')
            ->andReturn(false);

        $laravelResponse = new Response("test");

        return [$laravelRequestMock, $laravelResponse];
    }

    public function testCachePostEventShouldCache()
    {
        $this->mockRequest->server = [
            'request_uri' => '/',
            'request_method' => 'get'
        ];

        $this->caching->cacheRoute('/');

        [, $laravelResponse] = $this->getLaravelRequestMocks();

        $laravelRequestMock = \Mockery::mock(\Laravel\Lumen\Http\Request::class);


        $laravelRequestMock->shouldReceive('is')
            ->andReturn(true);

        $postEvent = $this->caching->cachePostEvent();


        $response = $postEvent(
            $this->mockRequest,
            $this->mockResponse,
            $laravelRequestMock,
            $laravelResponse

        );



        $this->assertTrue($response);

    }

    public function testCachePostEventShouldNotCache()
    {

        [$laravelRequestMock, $laravelResponse] = $this->getLaravelRequestMocks();


        $postEvent = $this->caching->cachePostEvent();


        $response = $postEvent(
            $this->mockRequest,
            $this->mockResponse,
            $laravelRequestMock,
            $laravelResponse

        );


        $this->assertFalse($response);

    }
}