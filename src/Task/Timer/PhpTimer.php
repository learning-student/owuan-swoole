<?php


namespace SwooleTW\Http\Task\Timer;


use SwooleTW\Http\Task\TimerInterface;

class PhpTimer implements TimerInterface
{

    /**
     * @param int $seconds
     * @param callable $callback
     */
    public function after(int $seconds, callable $callback): void
    {
        sleep($seconds);

        $callback();
    }
}