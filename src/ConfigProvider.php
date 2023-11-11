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


use function Hyperf\Support\env;

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
            'server' => [
                'settings' => [
                    // 静态资源
                    'document_root' => BASE_PATH . '/vendor/guandeng/hyperf-telescope/public',
                    'enable_static_handler' => true,
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
            'databases' => [
                'telescope' => [
                    'driver' => env('DB_TELESCOPE_CONNECTION', 'mysql'),
                    'host' => env('DB_TELESCOPE_HOST', 'localhost'),
                    'database' => env('DB_TELESCOPE_DATABASE', 'hyperf'),
                    'port' => env('DB_TELESCOPE_PORT', 3306),
                    'username' => env('DB_TELESCOPE_USERNAME', 'root'),
                    'password' => env('DB_TELESCOPE_PASSWORD', ''),
                    'charset' => env('DB_TELESCOPE_CHARSET', 'utf8'),
                    'collation' => env('DB_TELESCOPE_COLLATION', 'utf8_unicode_ci'),
                    'prefix' => env('DB_TELESCOPE_PREFIX', ''),
                    'pool' => [
                        'min_connections' => env('TELESCOPE_MIN_CONNECTIONS', 1),
                        'max_connections' => env('TELESCOPE_MAX_CONNECTIONS', 128),
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
                    ],
                ],
            ],
        ];
    }
}
