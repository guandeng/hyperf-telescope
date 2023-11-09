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

class ClearCommand extends Command
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct('telescope:clear');

        $this->container = $container;
    }

    public function handle()
    {
        Db::connection('telescope')->table('telescope_entries')->truncate();
        Db::connection('telescope')->table('telescope_entries_tags')->truncate();
        Db::connection('telescope')->table('telescope_monitoring')->truncate();
    }
}
