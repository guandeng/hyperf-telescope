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

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'commands' => [
                Command\ClearCommand::class,
                Command\InstallCommand::class,
                Command\PruneCommand::class,
            ],
            'listeners' => [
                Listener\CheckIsEnableRequestLifecycleListener::class,
                Listener\CommandListener::class,
                Listener\DbQueryListener::class,
                Listener\ExceptionHandlerListener::class,
                Listener\SetupTelescopeServerListener::class,
            ],
            'aspects' => [
                Aspect\GrpcClientAspect::class,
                Aspect\RedisAspect::class,
                Aspect\LogAspect::class,
                Aspect\EventAspect::class,
                Aspect\HttpClientAspect::class,
            ],
            'view' => [
                'engine' => TemplateEngine::class,
                'mode' => \Hyperf\View\Mode::SYNC,
                'config' => [
                    'view_path' => BASE_PATH . '/vendor/guandeng/hyperf-telescope/storage/view/',
                    'cache_path' => BASE_PATH . '/runtime/view/',
                ],
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file for hyperf telescope',
                    'source' => __DIR__ . '/../publish/telescope.php',
                    'destination' => BASE_PATH . '/config/autoload/telescope.php',
                ],
            ],
        ];
    }
}
