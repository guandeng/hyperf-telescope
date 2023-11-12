<?php

declare(strict_types=1);
/**
 * This file is part of guandeng/hyperf-telescope.
 *
 * @link     https://github.com/guandeng/hyperf-telescope
 * @document https://github.com/guandeng/hyperf-telescope/blob/main/README.md
 * @contact  guandeng@gmail.com
 */

namespace Guandeng\Telescope\Command;

use Hyperf\Command\Command;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;

use function Hyperf\Config\config;

class ClearCommand extends Command
{
    public function __construct(private ContainerInterface $container)
    {
        parent::__construct('telescope:clear');
    }

    public function handle()
    {
        $connection = config('telescope.database.connection');
        Db::connection($connection)->table('telescope_entries')->truncate();
        Db::connection($connection)->table('telescope_entries_tags')->truncate();
        Db::connection($connection)->table('telescope_monitoring')->truncate();
    }
}
