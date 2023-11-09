<?php

declare(strict_types=1);
/**
 * This file is part of guandeng/hyperf-telescope.
 *
 * @link     https://github.com/guandeng/hyperf-telescope
 * @document https://github.com/guandeng/hyperf-telescope/blob/main/README.md
 * @contact  guandeng@gmail.com
 */

namespace Guandeng\Telescope\Listener;

use Guandeng\Telescope\EntryType;
use Guandeng\Telescope\IncomingEntry;
use Guandeng\Telescope\TelescopeContext;
use Hyperf\Collection\Arr;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\HttpServer\Event\RequestTerminated;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ResponsePlusInterface;

use function Hyperf\Collection\collect;
use function Hyperf\Support\env;

class RequestHandledListener implements ListenerInterface
{
    public function __construct(protected ConfigInterface $config)
    {
    }

    public function listen(): array
    {
        return [
            RequestReceived::class,
            RequestTerminated::class,
        ];
    }

    public function process(object $event): void
    {
        if (env('TELESCOPE_ENABLED', false) === false) {
            return;
        }
        match ($event::class) {
            RequestReceived::class => $this->requestReceived($event),
            RequestTerminated::class => $this->requestHandled($event),
            default => '', // fix phpstan error
        };
    }

    public function requestReceived($event)
    {
        /**
         * @var ServerRequestInterface $request
         */
        $request = $event->request;
        $batchId = $request->getHeaderLine('batch-id');
        if (! $batchId) {
            $batchId = Str::orderedUuid()->toString();
        } else {
            $subBatchId = Str::orderedUuid()->toString();
            TelescopeContext::setSubBatchId($subBatchId);
        }
        TelescopeContext::setBatchId($batchId);
    }

    /**
     * @param RequestTerminated $event
     */
    public function requestHandled($event)
    {
        if ($event->response instanceof ResponsePlusInterface && $batchId = TelescopeContext::getBatchId()) {
            $event->response->addHeader('batch-id', $batchId);
        }
        /**
         * @var \Hyperf\HttpMessage\Server\Request $psr7Request
         */
        $psr7Request = $event->request;
        $psr7Response = $event->response;
        $middlewares = $this->config->get('middlewares.' . $event->server, []);
        $startTime = $psr7Request->getServerParams()['request_time_float'];
        if ($this->incomingRequest($psr7Request)) {
            /** @var Dispatched $dispatched */
            $dispatched = $psr7Request->getAttribute(Dispatched::class);

            $entry = IncomingEntry::make([
                'ip_address' => $psr7Request->getServerParams()['remote_addr'],
                'uri' => $psr7Request->getRequestTarget(),
                'method' => $psr7Request->getMethod(),
                'controller_action' => $dispatched->handler ? $dispatched->handler->callback : '',
                'middleware' => $middlewares,
                'headers' => $psr7Request->getHeaders(),
                'payload' => $psr7Request->getParsedBody(),
                'session' => '',
                'response_status' => $psr7Response->getStatusCode(),
                'response' => $this->response($psr7Response),
                'duration' => $startTime ? floor((microtime(true) - $startTime) * 1000) : null,
                'memory' => round(memory_get_peak_usage(true) / 1024 / 1025, 1),
            ]);
            $batchId = (string) TelescopeContext::getBatchId();
            $subBatchId = (string) TelescopeContext::getSubBatchId();
            $type = $this->getType($psr7Request);
            $entry->batchId($batchId)->subBatchId($subBatchId)->type($type)->user();
            $entry->create();

            $this->queryRecord($batchId);
            $this->exceptionRecord($batchId);
            $this->redisRecord($batchId);
            $this->loggerRecord($batchId);
            $this->eventRecord($batchId);
            $this->commandRecord($batchId);
            $this->clientReuestsRecord($batchId);
        }
    }

    public function getAppName(): string
    {
        return $this->config->get('telescope.app.name', '');
    }

    public function getType($psr7Request)
    {
        if (Str::contains($psr7Request->getHeaderLine('content-type'), 'application/grpc')) {
            return EntryType::SERVICE;
        }
        return EntryType::REQUEST;
    }

    protected function queryRecord(string $batchId = ''): void
    {
        $arr = Context::get('query_record', []);
        $optionSlow = env('TELESCOPE_QUERY_SLOW', 500);
        foreach ($arr as [$event, $sql]) {
            Coroutine::create(function () use ($batchId, $event, $sql, $optionSlow) {
                $entry = IncomingEntry::make([
                    'connection' => $event->connectionName,
                    'bindings' => [],
                    'sql' => '[' . $this->getAppName() . '] ' . $sql,
                    'time' => number_format($event->time, 2, '.', ''),
                    'slow' => $event->time >= $optionSlow,
                    'hash' => md5($sql),
                ]);
                $subBatchId = (string) TelescopeContext::getSubBatchId();
                $entry->batchId($batchId)->subBatchId($subBatchId)->type(EntryType::QUERY)->user();
                $entry->create();
            });
        }
    }

    protected function exceptionRecord(string $batchId = ''): void
    {
        $exception = Context::get('exception_record');
        if ($exception) {
            $trace = collect($exception->getTrace())->map(function ($item) {
                return Arr::only($item, ['file', 'line']);
            })->toArray();

            $entry = IncomingEntry::make([
                'class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'context' => null,
                'trace' => $trace,
                'line_preview' => $this->getContext($exception),
            ]);
            $subBatchId = (string) TelescopeContext::getSubBatchId();
            $entry->batchId($batchId)->subBatchId($subBatchId)->type(EntryType::EXCEPTION)->user();
            $entry->create();
        }
    }

    protected function redisRecord(string $batchId = ''): void
    {
        $arr = Context::get('redis_record', []);
        foreach ($arr as [$time,$command]) {
            Coroutine::create(function () use ($batchId, $time, $command) {
                $entry = IncomingEntry::make([
                    'command' => '[' . $this->getAppName() . '] ' . $command,
                    'time' => $time,
                    'hash' => md5($command),
                ]);
                $subBatchId = (string) TelescopeContext::getSubBatchId();
                $entry->batchId($batchId)->subBatchId($subBatchId)->type(EntryType::REDIS)->user();
                $entry->create();
            });
        }
    }

    protected function eventRecord(string $batchId = ''): void
    {
        $arr = Context::get('event_record', []);
        foreach ($arr as [$listenName,$eventName,$payload]) {
            Coroutine::create(function () use ($batchId, $listenName, $eventName, $payload) {
                $entry = IncomingEntry::make([
                    'name' => '[' . $this->getAppName() . '] ' . $eventName,
                    'listeners' => $listenName,
                    'payload' => $payload,
                    'hash' => md5($eventName),
                ]);
                $subBatchId = (string) TelescopeContext::getSubBatchId();
                $entry->batchId($batchId)->subBatchId($subBatchId)->type(EntryType::EVENT)->user();
                $entry->create();
            });
        }
    }

    protected function commandRecord(string $batchId = ''): void
    {
        $arr = Context::get('command_record', []);
        foreach ($arr as [$command, $arguments, $options, $exit_code]) {
            Coroutine::create(function () use ($batchId, $command, $arguments, $options, $exit_code) {
                $entry = IncomingEntry::make([
                    'command' => '[' . $this->getAppName() . '] ' . $command,
                    'exit_code' => $exit_code,
                    'arguments' => $arguments,
                    'options' => $options,
                ]);
                $subBatchId = (string) TelescopeContext::getSubBatchId();
                $entry->batchId($batchId)->subBatchId($subBatchId)->type(EntryType::COMMAND)->user();
                $entry->create();
            });
        }
    }

    protected function clientReuestsRecord(string $batchId = ''): void
    {
        $arr = Context::get('client_requests_record', []);
        foreach ($arr as [$method, $uri,$headers,$response_status,$duration]) {
            Coroutine::create(function () use ($batchId, $method, $uri, $headers, $response_status, $duration) {
                $entry = IncomingEntry::make([
                    'method' => $method,
                    'uri' => '[' . $this->getAppName() . '] ' . $uri,
                    'headers' => $headers,
                    'response_status' => $response_status,
                    'duration' => $duration,
                ]);
                $subBatchId = (string) TelescopeContext::getSubBatchId();
                $entry->batchId($batchId)->subBatchId($subBatchId)->type(EntryType::CLIENT_REQUEST)->user();
                $entry->create();
            });
        }
    }

    protected function loggerRecord(string $batchId = ''): void
    {
        $arr = Context::get('log_record', []);
        foreach ($arr as [$level,$message,$context]) {
            Coroutine::create(function () use ($batchId, $level, $message, $context) {
                $entry = IncomingEntry::make([
                    'message' => '[' . $this->getAppName() . '] ' . $message,
                    'context' => Arr::except($context, ['telescope']),
                    'level' => $level,
                    'time' => 0,
                    'hash' => md5($message),
                ]);
                $subBatchId = (string) TelescopeContext::getSubBatchId();
                $entry->batchId($batchId)->subBatchId($subBatchId)->type(EntryType::LOG)->user();
                $entry->create();
            });
        }
    }

    protected function incomingRequest($psr7Request)
    {
        $target = $psr7Request->getRequestTarget();
        if (Str::contains($target, '.ico')) {
            return false;
        }

        if (Str::contains($target, 'telescope') !== false) {
            return false;
        }

        return true;
    }

    protected function response(ResponseInterface $response)
    {
        $content = $response->getBody()->getContents();
        if (! $this->contentWithinLimits($content)) {
            return 'Purged By Hyperf Telescope';
        }

        if (is_string($content) && Str::contains($response->getHeaderLine('content-type'), 'application/json') !== false) {
            if (
                is_array(json_decode($content, true))
                && json_last_error() === JSON_ERROR_NONE
            ) {
                return json_decode($content, true);
            }
        }
        return $content;
    }

    protected function getContext($exception)
    {
        if (Str::contains($exception->getFile(), "eval()'d code")) {
            return [
                $exception->getLine() => "eval()'d code",
            ];
        }
        return collect(explode("\n", file_get_contents($exception->getFile())))
            ->slice($exception->getLine() - 10, 20)
            ->mapWithKeys(function ($value, $key) {
                return [$key + 1 => $value];
            })->all();
    }

    protected function contentWithinLimits($content)
    {
        $limit = 64;
        return mb_strlen($content) / 1000 <= $limit;
    }
}
