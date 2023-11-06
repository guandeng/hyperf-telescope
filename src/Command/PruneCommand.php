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

namespace Guandeng\Telescope\Command;

use Carbon\Carbon;
use Hyperf\Command\Command;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;

class PruneCommand extends Command
{
    protected $container;

    protected ?string $signature = 'telescope:prune {--hours=24 : The number of hours to retain Telescope data}';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    public function handle()
    {
        $created_at = Carbon::now()->subHours($this->option('hours'));
        Db::connection('telescope')->table('telescope_entries')
            ->where('created_at', '<', $created_at)
            ->delete();
        Db::connection('telescope')
            ->table('telescope_entries_tags')
            ->delete();
        Db::connection('telescope')
            ->table('telescope_monitoring')
            ->delete();
    }
}
