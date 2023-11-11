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
use Hyperf\Collection\Arr;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event;
use Hyperf\Stringable\Str;
use Throwable;

use function Hyperf\Collection\collect;

class ExceptionHandlerListener implements ListenerInterface
{
    public function __construct(private SwitchManager $switchManager)
    {
    }

    public function listen(): array
    {
        return [
            Event\RequestReceived::class,
            Event\RequestTerminated::class,
        ];
    }

    /**
     * @param Event\RequestTerminated $event
     */
    public function process(object $event): void
    {
        if ($this->switchManager->isEnabled() === false) {
            return;
        }
        if ($event instanceof Event\RequestTerminated && $event->exception instanceof Throwable) {
            $exception = $event->exception;

            $trace = collect($exception->getTrace())->map(function ($item) {
                return Arr::only($item, ['file', 'line']);
            })->toArray();

            Telescope::recordException(IncomingEntry::make([
                'class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'context' => null,
                'trace' => $trace,
                'line_preview' => $this->getContext($exception),
            ]));
        }
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
}