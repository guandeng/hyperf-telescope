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

use GuzzleHttp\Client;
use Hyperf\Context\Context;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

use function Hyperf\Tappable\tap;

class HttpClientAspect extends AbstractAspect
{
    public array $classes = [
        Client::class . '::request',
        Client::class . '::requestAsync',
    ];

    public function __construct(private ContainerInterface $container)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            $options = $proceedingJoinPoint->arguments['keys']['options'];
            if (isset($options['no_aspect']) && $options['no_aspect'] === true) {
                return;
            }
            $arguments = $proceedingJoinPoint->arguments;
            $method = $arguments['keys']['method'] ?? 'Null';
            $uri = $arguments['keys']['uri'] ?? 'Null';
            $headers = $options['headers'] ?? [];
            // to do
            $response_status = 200;
            $duration = 0;
            $arr = Context::get('client_request_record', []);
            $arr[] = [$method, $uri, $headers, $response_status, $duration];
            Context::set('client_request_record', $arr);
        });
    }
}
