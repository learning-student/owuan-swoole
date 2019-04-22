<?php


namespace SwooleTW\Http\Event;

use SwooleTW\Http\Server\Facades\Server;
use Symfony\Component\EventDispatcher\Tests\EventTest;

/**
 * Class Event
 * @package SwooleTW\Http\Event
 */
class Event
{

    /**
     * Dispatch an event and call the listeners.
     *
     * @param string|object $event
     * @return array|null
     */
    public function fire(EventBase $event): bool
    {

        app()->make(Server::class)
            ->task($event);

        return true;
    }
}