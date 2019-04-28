<?php

namespace SwooleTW\Http\Server;

use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use function PHPSTORM_META\override;
use Throwable;
use Swoole\Process;
use Swoole\Server\Task;
use Illuminate\Support\Str;
use SwooleTW\Http\Helpers\OS;
use SwooleTW\Http\Task\SwooleTaskJob;
use Illuminate\Support\Facades\Facade;
use SwooleTW\Http\Websocket\Websocket;
use SwooleTW\Http\Transformers\Request;
use SwooleTW\Http\Server\Facades\Server;
use SwooleTW\Http\Transformers\Response;
use SwooleTW\Http\Concerns\WithApplication;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use SwooleTW\Http\Concerns\InteractsWithWebsocket;
use Symfony\Component\Console\Output\ConsoleOutput;
use SwooleTW\Http\Concerns\InteractsWithSwooleQueue;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use SwooleTW\Http\Event\EventBase;

/**
 * Class Manager
 */
class Manager extends Event
{
    use InteractsWithWebsocket,
        InteractsWithSwooleQueue,
        WithApplication;

    /**
     * Container.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * @var string
     */
    protected $framework;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var bool
     */
    protected $started = false;
    /**
     * Server events.
     *
     * @var array
     */
    protected $events = [
        'start',
        'shutDown',
        'workerStart',
        'workerStop',
        'packet',
        'bufferFull',
        'bufferEmpty',
        'task',
        'finish',
        'pipeMessage',
        'workerError',
        'managerStart',
        'managerStop',
        'request',
    ];

    /**
     * HTTP server manager constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param string $framework
     * @param string $basePath
     *
     * @throws \Exception
     */
    public function __construct(Container $container, $framework, $basePath = null)
    {
        $this->container = $container;
        $this->setFramework($framework);
        $this->setBasepath($basePath);
    }


    /**
     * Run swoole server.
     */
    public function run()
    {
        // initialize  listeners and server
        $this->initialize();


        $this->container->make(Server::class)->start();
    }

    /**
     * Stop swoole server.
     */
    public function stop()
    {
        $this->container->make(Server::class)->shutdown();
    }

    /**
     * Initialize.
     */
    public function initialize()
    {
        if ($this->started === true) {
            return null;
        }

        $this->prepareWebsocket();
        $this->setSwooleServerListeners();

        $this->started = true;
    }

    /**
     * Set swoole server listeners.
     */
    protected function setSwooleServerListeners()
    {

        foreach ($this->events as $event) {
            $listener = Str::camel("on_$event");

            $callback = method_exists($this, $listener) ? [$this, $listener] : function () use ($event) {

                /**
                 * @var $eventBase Dispatcher
                 */

                $eventBase = $this->container->make('events');

                $eventBase->dispatch("swoole.$event", func_get_args());
            };

            $this->container->make(Server::class)->on($event, $callback);
        }
    }

    /**
     * "onStart" listener.
     */
    public function onStart()
    {
        $this->setProcessName('master process');
        $this->createPidFile();

        $this->container->make('events')->dispatch('swoole.start', func_get_args());
    }

    /**
     * The listener of "managerStart" event.
     *
     * @return void
     */
    public function onManagerStart()
    {
        $this->setProcessName('manager process');
        $this->container->make('events')->dispatch('swoole.managerStart', func_get_args());
    }

    /**
     * "onWorkerStart" listener.
     *
     * @param \Swoole\Http\Server|mixed $server
     *
     * @throws \Exception
     */
    public function onWorkerStart($server)
    {
        $this->clearCache();

        $this->container->make('events')->dispatch('swoole.workerStart', func_get_args());

        // don't init laravel app in task workers
        if ($server->taskworker) {
            $this->setProcessName('task process');
        } else {
            $this->setProcessName('worker process');
        }

        // clear events instance in case of repeated listeners in worker process
        Facade::clearResolvedInstance('events');

        // prepare laravel app
        $this->getApplication();

        // bind after setting laravel app
        $this->bindToLaravelApp();

        // prepare websocket handler and routes
        if ($this->isServerWebsocket) {
            $this->prepareWebsocketHandler();
            $this->loadWebsocketRoutes();
        }
    }


    /**
     * @param \Swoole\Http\Request $swooleRequest
     * @param \Swoole\Http\Response $swooleResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function onRequest($swooleRequest, $swooleResponse)
    {
        $this->app->make('events')->dispatch('swoole.request');

        $this->resetOnRequest();

        // create sandbox
        $handleStatic = $this->container->make('config')->get('swoole_http.handle_static_files', true);
        $publicPath = $this->container->make('config')->get('swoole_http.server.public_path', base_path('public'));

        // we don't need to create sandbox to be created up there,
        $sandbox = $this->app->make(Sandbox::class);
        try {


            // handle static file request first
            if ($handleStatic && Request::handleStatic($swooleRequest, $swooleResponse, $publicPath)) {
                return;
            }


            // run through pre events, and stop if a response returned
            // this is useful to cache, with cache we won't have to go through all laravel
            // lifecycle and send cached results
            // of course we don't want to cache static files, so this line always
            // should come after Request::handleStatic
            $response = $this->runPreEventsWithParams('request', [
                $swooleRequest,
                $swooleResponse
            ], true);


            if ($response instanceof \Illuminate\Http\Response) {
                Response::make($response, $swooleResponse)->send();

                return;
            }


            // transform swoole request to illuminate request
            $illuminateRequest = Request::make($swooleRequest)->toIlluminate();

            // set current request to sandbox
            $sandbox->setRequest($illuminateRequest);

            // enable sandbox
            $sandbox->enable();

            // handle request via laravel/lumen's dispatcher
            $illuminateResponse = $sandbox->run($illuminateRequest);

            // send response
            Response::make($illuminateResponse, $swooleResponse)->send();

            // run through post events

            $this->runPostEventsWithParams('request', [
                $swooleRequest,
                $swooleResponse,
                $illuminateRequest,
                $illuminateResponse
            ]);

        } catch (Throwable $e) {
            // transform swoole request to illuminate request
            $illuminateRequest = Request::make($swooleRequest)->toIlluminate();


            try {
                $exceptionResponse = $this->app
                    ->make(ExceptionHandler::class)
                    ->render(
                        $illuminateRequest,
                        $this->normalizeException($e)
                    );
                Response::make($exceptionResponse, $swooleResponse)->send();
            } catch (Throwable $e) {
                $this->logServerError($e);
            }
        } finally {
            // disable and recycle sandbox resource
            $sandbox->disable();
        }
    }

    /**
     * Reset on every request.
     */
    protected function resetOnRequest()
    {
        // Reset websocket data
        if ($this->isServerWebsocket) {
            $this->app->make(Websocket::class)->reset(true);
        }
    }

    /**
     * Set onTask listener.
     *
     * @param mixed $server
     * @param string|\Swoole\Server\Task $taskId or $task
     * @param string $srcWorkerId
     * @param mixed $data
     * @throws
     */
    public function onTask($server, $taskId, $srcWorkerId, $data)
    {


        $this->container->make('events')->dispatch('swoole.task', func_get_args());

        try {

            // push websocket message
            if ($this->isWebsocketPushPayload($data)) {
                $this->pushMessage($server, $data['data']);
                // push async task to queue
            } elseif ($data instanceof EventBase) {

                // resolve event in this worker
                event($data);

            } elseif ($this->isAsyncTaskPayload($data)) {
                (new SwooleTaskJob($this->container, $server, $data, $taskId, $srcWorkerId))->fire();
            }
        } catch (Throwable $e) {
            $this->logServerError($e);
        }
    }

    /**
     * Set onFinish listener.
     *
     * @param mixed $server
     * @param string $taskId
     * @param mixed $data
     */
    public function onFinish($server, $taskId, $data)
    {
        // task worker callback
        $this->container->make('events')->dispatch('swoole.finish', func_get_args());


        return true;
    }

    /**
     * Set onShutdown listener.
     */
    public function onShutdown()
    {
        $this->removePidFile();
    }

    /**
     * Set bindings to Laravel app.
     */
    protected function bindToLaravelApp()
    {
        $this->bindSandbox();

        if ($this->isServerWebsocket) {
            $this->bindRoom();
            $this->bindWebsocket();
        }
    }

    /**
     * Bind sandbox to Laravel app container.
     */
    protected function bindSandbox()
    {
        $this->app->singleton(Sandbox::class, function ($app) {
            return new Sandbox($app, $this->framework);
        });

        $this->app->alias(Sandbox::class, 'swoole.sandbox');
    }

    /**
     * Gets pid file path.
     *
     * @return string
     */
    protected function getPidFile()
    {
        return $this->container->make('config')->get('swoole_http.server.options.pid_file');
    }

    /**
     * Create pid file.
     */
    protected function createPidFile()
    {
        $pidFile = $this->getPidFile();
        $pid = $this->container->make(Server::class)->master_pid;

        file_put_contents($pidFile, $pid);
    }

    /**
     * Remove pid file.
     */
    protected function removePidFile()
    {
        $pidFile = $this->getPidFile();

        if (file_exists($pidFile)) {
            unlink($pidFile);
        }
    }

    /**
     * Clear APC or OPCache.
     */
    protected function clearCache()
    {
        if (extension_loaded('apc')) {
            apc_clear_cache();
        }

        if (extension_loaded('Zend OPcache')) {
            opcache_reset();
        }
    }

    /**
     * Set process name.
     *
     * @codeCoverageIgnore
     *
     * @param $process
     */
    protected function setProcessName($process)
    {
        // MacOS doesn't support modifying process name.
        if (OS::is(OS::MAC_OS) || $this->isInTesting()) {
            return;
        }

        $serverName = 'swoole_http_server';
        $appName = $this->container->make('config')->get('app.name', 'Laravel');

        $name = sprintf('%s: %s for %s', $serverName, $process, $appName);

        swoole_set_process_name($name);
    }

    /**
     * Add process to http server
     *
     * @param \Swoole\Process $process
     */
    public function addProcess(Process $process): void
    {
        $this->container->make(Server::class)->addProcess($process);
    }

    /**
     * Indicates if it's in phpunit environment.
     *
     * @return bool
     */
    protected function isInTesting()
    {
        return defined('IN_PHPUNIT') && IN_PHPUNIT;
    }

    /**
     * Log server error.
     *
     * @param \Throwable|\Exception $e
     */
    public function logServerError(Throwable $e)
    {
        if ($this->isInTesting()) {
            return;
        }

        $exception = $this->normalizeException($e);
        $this->container->make(ConsoleOutput::class)
            ->writeln(sprintf("<error>%s</error>", $exception));

        $this->container->make(ExceptionHandler::class)
            ->report($exception);
    }

    /**
     * Normalize a throwable/exception to exception.
     *
     * @param \Throwable|\Exception $e
     */
    protected function normalizeException(Throwable $e)
    {
        if (!$e instanceof Exception) {
            $e = new FatalThrowableError($e);
        }

        return $e;
    }

    /**
     * Indicates if the payload is async task.
     *
     * @param mixed $payload
     *
     * @return boolean
     */
    protected function isAsyncTaskPayload($payload): bool
    {
        $data = json_decode($payload, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return false;
        }

        return isset($data['job']);
    }
}
