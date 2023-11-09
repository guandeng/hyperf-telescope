<?php

declare(strict_types=1);
/**
 * This file is part of guandeng/hyperf-telescope.
 *
 * @link     https://github.com/guandeng/hyperf-telescope
 * @document https://github.com/guandeng/hyperf-telescope/blob/main/README.md
 * @contact  guandeng@gmail.com
 */

namespace Guandeng\Telescope\Aspect;

use Hyperf\Context\Context;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Event\EventDispatcher;
use Hyperf\Stringable\Str;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionClass;

use function Hyperf\Collection\collect;
use function Hyperf\Tappable\tap;

class EventAspect extends AbstractAspect
{
    public array $classes = [
        EventDispatcher::class . '::dispatch',
    ];

    public function __construct(private ContainerInterface $container, private ListenerProviderInterface $listeners)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            $event = $proceedingJoinPoint->arguments['keys']['event'];
            $eventName = get_class($event);
            $listenerNames = [];
            foreach ($this->listeners->getListenersForEvent($event) as $listener) {
                $listenerNames[] = $this->getListenerName($listener);
            }
            if (Str::contains($eventName, 'Hyperf\\')) {
                return;
            }
            $ref = new ReflectionClass($event);

            $c = $ref->getConstructor();
            $payload = $c->getParameters();
            $payload = $this->extractPayload($eventName, $payload);
            $arr = Context::get('event_record', []);
            $arr[] = [$listenerNames, $eventName, $payload];
            Context::set('event_record', $arr);
        });
    }

    protected function extractPayload($eventName, $payload)
    {
        // to do
        // if (isset($payload[0]) && is_object($payload[0])) {
        //     return ExtractProperties::from($payload[0]);
        // }

        return collect($payload)->map(function ($value) {
            return is_object($value) ? [
                'class' => get_class($value),
                'properties' => json_decode(json_encode($value), true),
            ] : $value;
        })->toArray();
    }

    protected function getListenerName($listener)
    {
        $listenerName = '[ERROR TYPE]';
        if (is_array($listener)) {
            $listenerName = is_string($listener[0]) ? $listener[0] : get_class($listener[0]);
        } elseif (is_string($listener)) {
            $listenerName = $listener;
        } elseif (is_object($listener)) {
            $listenerName = get_class($listener);
        }
        return ['name' => $listenerName];
    }
}
