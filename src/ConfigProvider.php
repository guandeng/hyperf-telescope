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

use Guandeng\Telescope\Listener\CheckIsEnableRequestLifecycleListener;

use function Hyperf\Support\env;

class ConfigProvider
{
    public function __invoke(): array
    {
        if (env('TELESCOPE_ENABLED', false) === false) {
            return [];
        }

        return [
            'commands' => [
                \Guandeng\Telescope\Command\ClearCommand::class,
                \Guandeng\Telescope\Command\InstallCommand::class,
                \Guandeng\Telescope\Command\PruneCommand::class,
            ],
            'listeners' => [
                \Guandeng\Telescope\Listener\QueryListener::class,
                CheckIsEnableRequestLifecycleListener::class,
            ],
            'aspects' => [
                \Guandeng\Telescope\Aspect\GrpcClientAspect::class,
                \Guandeng\Telescope\Aspect\RedisAspect::class,
                \Guandeng\Telescope\Aspect\LogAspect::class,
            ],
            'view' => [
                'engine' => \Guandeng\Telescope\TemplateEngine::class,
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
            'exceptions' => [
                'handler' => [
                    'http' => [
                        \Guandeng\Telescope\Exception\ErrorRecord::class,
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
                        'min_connections' => 1,
                        'max_connections' => 10,
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
