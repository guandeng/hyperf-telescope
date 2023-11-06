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

namespace Guandeng\Telescope\Aspect;

use Guandeng\Telescope\TelescopeContext;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\GrpcClient\GrpcClient;
use Hyperf\GrpcClient\Request;
use Psr\Container\ContainerInterface;
use Throwable;

class GrpcClientAspect extends AbstractAspect
{
    public array $classes = [
        GrpcClient::class . '::send',
    ];

    public function __construct(private ContainerInterface $container) {}

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return match ($proceedingJoinPoint->methodName) {
            'send' => $this->processSend($proceedingJoinPoint),
            default => $proceedingJoinPoint->process(),
        };
    }

    private function processSend(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arguments = $proceedingJoinPoint->getArguments();
        /** @var Request $request */
        $request = $arguments[0];
        $carrier = [];
        $carrier['batch-id'] = TelescopeContext::getBatchId();
        $request->headers = array_merge($request->headers, $carrier);

        try {
            return $proceedingJoinPoint->process();
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
