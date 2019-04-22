<?php


namespace SwooleTW\Http\Tests\Event;


use Mockery as m;
use PHPUnit\Framework\TestCase;
use SwooleTW\Http\Event\Event;
use SwooleTW\Http\Event\EventBase;
use SwooleTW\Http\Tests\LaravelTestCase;
use SwooleTW\Http\Tests\Server\ManagerTest;

/**
 * Class EventTest
 * @package SwooleTW\Http\Tests\Event
 *
 */
class EventTest extends LaravelTestCase
{


    public function getEvent()
    {
        return new class extends EventBase
        {

        };
    }


    /**
     * @covers \SwooleTW\Http\Event\Event::fire
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testEventFireWhenServerNotStarted()
    {

        $this->expectException(\ErrorException::class);

        $event = new Event();
        
        $this->assertTrue(
            $event->fire($this->getEvent())
        );
        
    }
}