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

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\View\RenderInterface;
use Psr\Container\ContainerInterface;

#[Controller(server: 'telescope')]
class ViewController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

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

    #[GetMapping(path: '/vendor/telescope/{file}')]
    public function renderStaticFile(string $file)
    {
        $files = [
            'app.js' => [__DIR__ . '/../../public/vendor/telescope/app.js', 'application/javascript'],
            'app.css' => [__DIR__ . '/../../public/vendor/telescope/app.css', 'text/css'],
            'app-dark.css' => [__DIR__ . '/../../public/vendor/telescope/app-dark.css', 'text/css'],
        ];

        static $caches = [];

        if (! isset($caches[$file])) {
            if (! isset($files[$file]) || ! file_exists($files[$file][0])) {
                return $this->response->raw('')->withStatus(404);
            }

            $caches[$file] = (string) file_get_contents($files[$file][0]);
        }

        return $this->response->raw($caches[$file])->withHeader('Content-Type', $files[$file][1]);
    }
}
