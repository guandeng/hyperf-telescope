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
use Hyperf\Command\Command;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

use function Hyperf\Tappable\tap;

class CommandAspect extends AbstractAspect
{
    public array $classes = [
        Command::class . '::run',
    ];

    public function __construct(protected SwitchManager $switcherManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (! $this->switcherManager->isEnable('command')) {
                return;
            }

            $input = $proceedingJoinPoint->arguments['keys']['input'];

            Telescope::recordCommand(IncomingEntry::make([
                'command' => $input->getArguments()['command'] ?? 'default',
                'exit_code' => 0, // to do
                'arguments' => $input->getArguments(),
                'options' => $input->getOptions(),
            ]));
        });
    }
}
