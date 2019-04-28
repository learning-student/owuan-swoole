<?php


namespace SwooleTW\Http\Task;

/**
 * Interface TimerInterface
 * @package SwooleTW\Http\Task
 */
interface TimerInterface
{

    /**
     * @param int $seconds
     * @param callable $callback
     */
    public function after(int $seconds, callable $callback) : void;

}