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

use Guandeng\Telescope\IncomingEntry;
use Guandeng\Telescope\SwitchManager;
use Guandeng\Telescope\Telescope;
use GuzzleHttp\Client;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

use function Hyperf\Tappable\tap;

class HttpClientAspect extends AbstractAspect
{
    public array $classes = [
        Client::class . '::request',
        Client::class . '::requestAsync',
    ];

    public function __construct(protected SwitchManager $switcherManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (! $this->switcherManager->isEnable('guzzle')) {
                return;
            }
            $options = $proceedingJoinPoint->arguments['keys']['options'];
            if (isset($options['no_aspect']) && $options['no_aspect'] === true) {
                return;
            }
            $arguments = $proceedingJoinPoint->arguments;
            $method = $arguments['keys']['method'] ?? 'Null';
            $uri = $arguments['keys']['uri'] ?? 'Null';
            $headers = $options['headers'] ?? [];

            Telescope::recordClientRequest(IncomingEntry::make([
                'method' => $method,
                'uri' => $uri,
                'headers' => $headers,
                'payload' => '',
                'response_status' => 0,
                'response_headers' => '',
                'response' => '',
                'duration' => 0,
            ]));
        });
    }
}
