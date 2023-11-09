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

use Hyperf\Command\Command;
use Hyperf\Context\Context;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

use function Hyperf\Tappable\tap;

class CommandAspect extends AbstractAspect
{
    public array $classes = [
        Command::class . '::run',
    ];

    public function __construct(private ContainerInterface $container)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            $input = $proceedingJoinPoint->arguments['keys']['input'];
            $command =  $input->getArguments()['command'] ?? 'default';
            $arguments = $input->getArguments();
            $options = $input->getOptions();
            // to do
            $exit_code = 0;
            $arr = Context::get('command_record', []);
            $arr[] = [$command, $arguments, $options, $exit_code];
            Context::set('command_record', $arr);
        });
    }
}
