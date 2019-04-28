<?php


namespace SwooleTW\Http\Tests\Task;


use Illuminate\Config\Repository;
use Illuminate\Queue\QueueManager;
use Orchestra\Testbench\TestCase;
use SwooleTW\Http\HttpServiceProvider;
use SwooleTW\Http\LaravelServiceProvider;
use SwooleTW\Http\Server\Facades\Server;
use SwooleTW\Http\Task\Connectors\SwooleTaskConnector;
use SwooleTW\Http\Task\SwooleTaskQueue;

class ConnectorTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            LaravelServiceProvider::class
        ];
    }

    /**
     * @param $name
     * @return \ReflectionMethod
     */
    protected static function getMethod($name) {
        $class = new \ReflectionClass(QueueManager::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testConnectorHasConnected()
    {


        /**
         * @var Repository $config
         */

        $config = $this->app->make('config');


        $config->set('queue.connections.swoole', [
            'driver' => 'swoole'
        ]);

        $queue = $this->app->make('queue');

        /**
         * @var QueueManager $queue
         */


        $method = static::getMethod('getConnector');


        $this->assertInstanceOf(QueueManager::class, $queue);


        $hasConnected = $method->invokeArgs($queue, ['swoole']);


        $this->assertInstanceOf(SwooleTaskConnector::class, $hasConnected);

        $connection = $queue->connection('swoole');

        $this->assertInstanceOf(SwooleTaskQueue::class, $connection);
    }

}