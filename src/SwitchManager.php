<?php

declare(strict_types=1);
/**
 * This file is part of guandeng/hyperf-telescope.
 *
 * @link     https://github.com/guandeng/hyperf-telescope
 * @document https://github.com/guandeng/hyperf-telescope/blob/main/README.md
 * @contact  guandeng@gmail.com
 */

namespace Guandeng\Telescope;

use Hyperf\Contract\ConfigInterface;

class SwitchManager
{
    public function __construct(protected ConfigInterface $config)
    {
    }

    public function isEnable(string $key): bool
    {
        return (bool) $this->config->get("telescope.enable.{$key}", true);
    }
}
