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

use Hyperf\View\Engine\EngineInterface;

use function Hyperf\Support\make;

class TemplateEngine implements EngineInterface
{
    public function render(string $template, array $data, array $config): string
    {
        return make(TemplateInstance::class, [$config['view_path']])->render($template, $data);
    }
}
