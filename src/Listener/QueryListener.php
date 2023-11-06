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

namespace Guandeng\Telescope\Listener;

use Hyperf\Collection\Arr;
use Hyperf\Context\Context;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Stringable\Str;

class QueryListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $event): void
    {
        if ($event instanceof QueryExecuted) {
            $sql = $event->sql;
            if (! Arr::isAssoc($event->bindings)) {
                foreach ($event->bindings as $key => $value) {
                    $sql = Str::replaceFirst('?', "'{$value}'", $sql);
                }
            }
            if (! Str::contains($sql, 'telescope')) {
                $arr = Context::get('query_record', []);

                $arr[] = [$event, $sql];

                Context::set('query_record', $arr);
            }
        }
    }
}
