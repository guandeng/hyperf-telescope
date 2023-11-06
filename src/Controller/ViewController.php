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
