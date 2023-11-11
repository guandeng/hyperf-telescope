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

use Guandeng\Telescope\SwitchManager;
use Guandeng\Telescope\TelescopeContext;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\GrpcClient\GrpcClient;
use Hyperf\GrpcClient\Request;

class GrpcClientAspect extends AbstractAspect
{
    public array $classes = [
        GrpcClient::class . '::send',
    ];

    public function __construct(protected SwitchManager $switcherManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return match ($proceedingJoinPoint->methodName) {
            'send' => $this->processSend($proceedingJoinPoint),
            default => $proceedingJoinPoint->process(),
        };
    }

    private function processSend(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($this->switcherManager->isEnable('grpc')) {
            $carrier = [];
            $carrier['batch-id'] = TelescopeContext::getBatchId();
            /** @var Request $request */
            $request = $proceedingJoinPoint->arguments['keys']['request'];
            $request->headers = array_merge($request->headers, $carrier);
            $proceedingJoinPoint->arguments['keys']['request'] = $request;
        }

        return $proceedingJoinPoint->process();
    }
}
