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

namespace Guandeng\Telescope;

use Exception;

class TemplateInstance
{
    protected $view_path;

    public function __construct($viewPath)
    {
        $this->view_path = $viewPath;
    }

    public function render($template, $data)
    {
        $loadFile = $this->view_path . $template . '.blade.php';
        if (! file_exists($loadFile)) {
            throw new Exception($loadFile . 'is not found');
        }

        return file_get_contents($loadFile);
    }
}
