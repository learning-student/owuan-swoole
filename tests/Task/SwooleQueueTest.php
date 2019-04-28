<?php

namespace SwooleTW\Http\Tests\Task;

use Mockery as m;
use SwooleTW\Http\Helpers\FW;
use SwooleTW\Http\Task\QueueFactory;
use SwooleTW\Http\Task\Timer\PhpTimer;
use SwooleTW\Http\Task\TimerInterface;
use SwooleTW\Http\Tests\TestCase;

class SwooleQueueTest extends TestCase
{


    public function testPushProperlyPushesJobOntoSwoole()
    {
        $server = $this->getServer();
        $queue = QueueFactory::make($server, FW::version());
        $server->shouldReceive('task')->once();
        $queue->push(new FakeJob);
    }



    public function testPushProperlyPushesJobOntoSwooleWithPhpLater()
    {
        $server = $this->getServer();
        $queue = QueueFactory::make($server, FW::version());
        $server->shouldReceive('task')->once();
        $queue->setTimer(new PhpTimer());
        $queue->later(1, new FakeJob);
    }

    protected function getServer()
    {
        $server = m::mock('server');
        $server->shouldReceive('on');
        $server->taskworker = false;
        $server->master_pid = -1;

        return $server;
    }
}


