<?php

declare(strict_types=1);
/**
 * This file is part of guandeng/hyperf-telescope.
 *
 * @link     https://github.com/guandeng/hyperf-telescope
 * @document https://github.com/guandeng/hyperf-telescope/blob/main/README.md
 * @contact  guandeng@gmail.com
 */
use function Hyperf\Support\env;

return [
    'app' => [
        'name' => env('APP_NAME', ''),
    ],
    'enable' => [
        'request' => env('TELESCOPE_ENABLE_REQUEST', false),
        'command' => env('TELESCOPE_ENABLE_COMMAND', false),
        'grpc' => env('TELESCOPE_ENABLE_GRPC', false),
        'log' => env('TELESCOPE_ENABLE_LOG', false),
        'redis' => env('TELESCOPE_ENABLE_REDIS', false),
        'event' => env('TELESCOPE_ENABLE_EVENT', false),
        'exception' => env('TELESCOPE_ENABLE_EXCEPTION', false),
        'job' => env('TELESCOPE_ENABLE_JOB', false),
        'db' => env('TELESCOPE_ENABLE_DB', false),
        'guzzle' => env('TELESCOPE_ENABLE_GUZZLE', false),
    ],
    'timezone' => env('TELESCOPE_TIMEZONE', 'Asia/Shanghai'),
    'query_slow' => env('TELESCOPE_QUERY_SLOW', 50),
    'database' => [
        'connection' => env('TELESCOPE_DB_CONNECTION', 'default'),
    ],
    'server' => [
        'host' => env('TELESCOPE_SERVER_HOST', '0.0.0.0'),
        'port' => (int) env('TELESCOPE_SERVER_PORT', 9509),
    ],
];
