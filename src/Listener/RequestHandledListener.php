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

use Guandeng\Telescope\IncomingEntry;
use Guandeng\Telescope\SwitchManager;
use Guandeng\Telescope\Telescope;
use Guandeng\Telescope\TelescopeContext;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\HttpServer\Event\RequestTerminated;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ResponsePlusInterface;

class RequestHandledListener implements ListenerInterface
{
    public function __construct(protected SwitchManager $switchManager, protected ConfigInterface $config)
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
        if (! $this->switchManager->isEnable('request')) {
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

            if (Str::contains($psr7Request->getHeaderLine('content-type'), 'application/grpc')) {
                Telescope::recordService($entry);
            } else {
                Telescope::recordRequest($entry);
            }
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
        $stream = $response->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        $content = $stream->getContents();
        if (is_string($content)) {
            if (! $this->contentWithinLimits($content)) {
                return 'Purged By Hyperf Telescope';
            }
            if (
                is_array(json_decode($content, true))
                && json_last_error() === JSON_ERROR_NONE
            ) {
                return $this->contentWithinLimits($content)
                ? $this->hideParameters(json_decode($content, true), Telescope::$hiddenResponseParameters)
                : 'Purged By Telescope';
            }
            if (Str::startsWith(strtolower($response->getHeaderLine('content-type') ?? ''), 'text/plain')) {
                return $this->contentWithinLimits($content) ? $content : 'Purged By Hyperf Telescope';
            }
            if (Str::contains($response->getHeaderLine('content-type'), 'application/grpc') !== false) {
                // to do for grpc
                return 'Purged By Hyperf Telescope';
            }
        }

        if (empty($content)) {
            return 'Empty Response';
        }

        return 'HTML Response';
    }

    protected function contentWithinLimits($content)
    {
        $limit = 64;
        return mb_strlen($content) / 1000 <= $limit;
    }

    /**
     * Hide the given parameters.
     *
     * @param array $data
     * @param array $hidden
     * @return mixed
     */
    protected function hideParameters($data, $hidden)
    {
        foreach ($hidden as $parameter) {
            if (Arr::get($data, $parameter)) {
                Arr::set($data, $parameter, '********');
            }
        }

        return $data;
    }
}
