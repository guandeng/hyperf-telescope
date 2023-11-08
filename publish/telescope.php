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
use function Hyperf\Support\env;

return [
    'app' => [
        'name' => env('APP_NAME', 'skeleton'),
    ],
    'enabled' => env('TELESCOPE_ENABLED', false),
    'timezone' => env('TELESCOPE_TIMEZONE', 'Asia/Shanghai'),
    'query_slow' => env('TELESCOPE_QUERY_SLOW', 50),
];
