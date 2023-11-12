<?php

namespace Guandeng\Telescope\Model;

use function Hyperf\Config\config;

abstract class Model extends \Hyperf\DbConnection\Model\Model
{
    public function getConnectionName()
    {
        return config('telescope.database.connection', 'default');
    }
}
