<?php


namespace SwooleTW\Http\Task\Timer;


use SwooleTW\Http\Task\TimerInterface;
use Swoole\Timer;

class SwooleTimer implements TimerInterface
{

    /**
     * @param int $seconds
     * @param callable $callback
     */
    public function after(int $seconds, callable $callback): void
    {
        Timer::after($seconds, $callback);
    }
}
