<?php

declare(strict_types=1);
/**
 * This file is part of guandeng/hyperf-telescope.
 *
 * @link     https://github.com/guandeng/hyperf-telescope
 * @document https://github.com/guandeng/hyperf-telescope/blob/main/README.md
 * @contact  guandeng@gmail.com
 */

namespace Guandeng\Telescope\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\View\RenderInterface;

#[Controller]
class ViewController
{
    #[GetMapping(path: '/telescope/{view}')]
    public function index(RenderInterface $render)
    {
        return $render->render('index');
    }

    #[GetMapping(path: '/telescope/{view}/{id}')]
    public function show(RenderInterface $render)
    {
        return $render->render('index');
    }
}
