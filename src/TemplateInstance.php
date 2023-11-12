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

use Exception;

class TemplateInstance
{
    public function __construct(protected string $viewPath)
    {
    }

    public function render($template, $data)
    {
        $templateFile = $this->viewPath . $template . '.blade.php';

        if (! file_exists($templateFile)) {
            throw new Exception($templateFile . 'is not found');
        }

        return file_get_contents($templateFile);
    }
}
