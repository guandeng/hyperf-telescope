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
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;

use function Hyperf\Collection\collect;
use function Hyperf\Tappable\tap;

class RedisAspect extends AbstractAspect
{
    public array $classes = [
        Redis::class . '::__call',
    ];

    public function __construct(private ContainerInterface $container)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $startTime = microtime(true);
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint, $startTime) {
            $arguments = $proceedingJoinPoint->arguments['keys'];
            $commands = $this->formatCommand($arguments['name'], $arguments['arguments']);
            $arr = Context::get('redis_record', []);
            $time = floor((microtime(true) - $startTime) * 1000);
            $arr[] = [$time, $commands];
            Context::set('redis_record', $arr);
        });
    }

    private function formatCommand($command, $parameters)
    {
        $parameters = collect($parameters)->map(function ($parameter) {
            if (is_array($parameter)) {
                return collect($parameter)->map(function ($value, $key) {
                    if (is_array($value)) {
                        return json_encode($value);
                    }

                    return is_int($key) ? $value : "{$key} {$value}";
                })->implode(' ');
            }

            return $parameter;
        })->implode(' ');

        return "{$command} {$parameters}";
    }
}
