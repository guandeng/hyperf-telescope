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
        'name' => env('APP_NAME', 'skeleton'),
    ],
    'enabled' => env('TELESCOPE_ENABLED', false),
    'timezone' => env('TELESCOPE_TIMEZONE', 'Asia/Shanghai'),
    'query_slow' => env('TELESCOPE_QUERY_SLOW', 50),
];
