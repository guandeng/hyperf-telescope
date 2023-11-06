<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Guandeng\Telescope\Middleware;

use Guandeng\Telescope\EntryType;
use Guandeng\Telescope\IncomingEntry;
use Guandeng\Telescope\TelescopeContext;
use Hyperf\Collection\Arr;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function Hyperf\Collection\collect;
use function Hyperf\Support\env;

class TelescopeMiddleware implements MiddlewareInterface
{
    public function __construct(protected ConfigInterface $config) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (env('TELESCOPE_ENABLED', false) === false) {
            return $handler->handle($request);
        }
        try {
            $batchId = $request->getHeaderLine('batch-id');
            if (! $batchId) {
                $batchId = Str::orderedUuid()->toString();
            } else {
                $subBatchId = Str::orderedUuid()->toString();
                TelescopeContext::setSubBatchId($subBatchId);
            }
            TelescopeContext::setBatchId($batchId);
            // response 属于最后处理
            $response = $handler->handle($request);
            $this->requestHandled($request, $response);
            if ($batchId) {
                $response = $response->withHeader('batch-id', $batchId);
            }
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
        }
        return $response;
    }

    public function requestHandled($request, $response)
    {
        /**
         * @var \Hyperf\HttpMessage\Server\Request $psr7Request
         */
        $psr7Request = $request;
        $psr7Response = $response;
        $startTime = $psr7Request->getServerParams()['request_time_float'];
        if ($this->incomingRequest($psr7Request)) {
            /** @var Dispatched $dispatched */
            $dispatched = $psr7Request->getAttribute(Dispatched::class);

            $entry = IncomingEntry::make([
                'ip_address' => $psr7Request->getServerParams()['remote_addr'],
                'uri' => $psr7Request->getRequestTarget(),
                'method' => $psr7Request->getMethod(),
                'controller_action' => $dispatched->handler ? $dispatched->handler->callback : '',
                'middleware' => '',
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
