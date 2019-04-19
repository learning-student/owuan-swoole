<?php


namespace SwooleTW\Http\Server;


use Illuminate\Http\Response;

/**
 * Class Event
 * @package SwooleTW\Http\Server
 */
abstract class Event
{
    /**
     * @var array<callable>
     */
    protected $preEvents;


    /**
     * @var array<callable>
     */
    protected $postEvents;

    /**
     * @param string $eventName
     * @param callable $callable
     * @return $this
     */
    final public function addPreEvent(string $eventName, callable $callable)
    {
        $this->preEvents[$eventName][] = $callable;

        return $this;
    }


    /**
     * @param string $eventName
     * @param callable $callable
     * @return $this
     */
    final public function addPostEvent(string $eventName, callable $callable)
    {
        $this->postEvents[$eventName][] = $callable;

        return $this;
    }


    /**
     * executes the event and returns the response
     *
     * @param callable $event
     * @param array $params
     * @return mixed
     */
    private function runEventAndReturnResponse(callable $event, array $params)
    {
        return call_user_func_array($event, $params);
    }

    /**
     * @param array $events
     * @param array<callable> $params
     * @param bool $shouldStopOnResponse
     * @return bool|mixed
     */
    private function runEvents(array $events, array $params, bool $shouldStopOnResponse = false)
    {

        foreach ($events as $event) {
            $response = $this->runEventAndReturnResponse(
                $event,
                $params
            );


            // laravel and lumen shares same response object
            if ($shouldStopOnResponse &&  $response instanceof Response) {
                return $response;
            }
        }

        return true;
    }

    /**
     * @param string $event
     * @param array $params
     * @param bool $shouldStopOnResponse
     * @return bool|mixed
     */
    final protected function runPreEventsWithParams(string $event, array $params = [], bool $shouldStopOnResponse = false)
    {
        $preEvents = $this->preEvents[$event] ?? [];

        return $this->runEvents($preEvents, $params, $shouldStopOnResponse);
    }

    /**
     * @param string $event
     * @param array $params
     * @param bool $shouldStopOnResponse
     * @return bool|mixed
     */
    final protected function runPostEventsWithParams(string $event, array $params = [], bool $shouldStopOnResponse = false)
    {
        $postEvents = $this->postEvents[$event] ?? [];

        return $this->runEvents($postEvents, $params, $shouldStopOnResponse);
    }
}