<?php

namespace SwooleTW\Http\Task;

use Exception;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Queue;
use SwooleTW\Http\Task\Timer\SwooleTimer;

/**
 * Class SwooleTaskQueue (5.7)
 */
class SwooleTaskQueue extends Queue implements QueueContract
{
    /**
     * Swoole Connector
     *
     * @var \Swoole\Http\Server
     */
    protected $swoole;

    /**
     * @var TimerInterface
     */
    protected $timer;

    /**
     * Create Async Task instance.
     *
     * @param \Swoole\Http\Server $swoole
     * @param TimerInterface $timer
     */
    public function __construct($swoole, TimerInterface $timer = null)
    {
        $this->swoole = $swoole;

        if (!$timer) {
            $this->setTimer(new SwooleTimer());
        }
    }

    /**
     * @return TimerInterface
     */
    public function getTimer(): TimerInterface
    {
        return $this->timer;
    }

    /**
     * @param TimerInterface $timer
     * @return SwooleTaskQueue
     */
    public function setTimer(TimerInterface $timer): SwooleTaskQueue
    {
        $this->timer = $timer;
        return $this;
    }


    /**
     * Push a new job onto the queue.
     *
     * @param string|object $job
     * @param mixed $data
     * @param string $queue
     *
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param string $payload
     * @param string $queue
     * @param array $options
     *
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->swoole->task($payload, !is_numeric($queue) ? -1 : (int)$queue);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @param string|object $job
     * @param mixed $data
     * @param string $queue
     *
     * @return void
     */
    public function later($delay, $job, $data = '', $queue = null): void
    {
        $this->getTimer()->after(
            $this->secondsUntil($delay),
            function () use ($job, $data, $queue) {
                return $this->push($job, $data, $queue);
            }
        );

    }

    /**
     * Create a typical, string based queue payload array.
     *
     * @param string $job
     * @param string $queue
     * @param mixed $data
     *
     * @throws \Exception
     */
    protected function createStringPayload($job, $queue, $data)
    {
        throw new Exception('Unsupported empty data');
    }

    /**
     * Get the size of the queue.
     *
     * @param string $queue
     *
     * @return int
     */
    public function size($queue = null)
    {
        return -1;
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string $queue
     *
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        return null;
    }
}
