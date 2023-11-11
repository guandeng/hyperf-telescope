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
        'request' => env('TELESCOPE_ENABLE_REQUEST', true),
        'command' => env('TELESCOPE_ENABLE_COMMAND', true),
        'grpc' => env('TELESCOPE_ENABLE_GRPC', true),
        'log' => env('TELESCOPE_ENABLE_LOG', true),
        'redis' => env('TELESCOPE_ENABLE_REDIS', true),
        'event' => env('TELESCOPE_ENABLE_EVENT', true),
        'exception' => env('TELESCOPE_ENABLE_EXCEPTION', true),
        'job' => env('TELESCOPE_ENABLE_JOB', true),
        'db' => env('TELESCOPE_ENABLE_DB', true),
        'guzzle' => env('TELESCOPE_ENABLE_GUZZLE', true),
    ],
    'enabled' => env('TELESCOPE_ENABLED', false),
    'timezone' => env('TELESCOPE_TIMEZONE', 'Asia/Shanghai'),
    'query_slow' => env('TELESCOPE_QUERY_SLOW', 50),
];
