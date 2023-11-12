<?php

declare(strict_types=1);
/**
 * This file is part of guandeng/hyperf-telescope.
 *
 * @link     https://github.com/guandeng/hyperf-telescope
 * @document https://github.com/guandeng/hyperf-telescope/blob/main/README.md
 * @contact  guandeng@gmail.com
 */

namespace Guandeng\Telescope\Model;

use function Hyperf\Config\config;

abstract class Model extends \Hyperf\DbConnection\Model\Model
{
    public function getConnectionName()
    {
        return config('telescope.database.connection', 'default');
    }
}
