<?php


namespace SwooleTW\Http\Tests\Event;


use PHPUnit\Framework\TestCase;
use SwooleTW\Http\Event\Event;
use SwooleTW\Http\Event\EventBase;

/**
 * Class EventTest
 * @package SwooleTW\Http\Tests\Event
 *
 */
class EventTest extends TestCase
{


    /**
     * @covers \SwooleTW\Http\Event\Event::fire
     */
    public function testEventFire()
    {
        $event = \Mockery::mock(Event::class);

        $event
            ->shouldReceive('fire')
            ->with(EventBase::class)
            ->once()
            ->andReturn(true);

        $anonymClass = new class extends EventBase{

        };

        $this->assertTrue($event->fire($anonymClass));


    }
}