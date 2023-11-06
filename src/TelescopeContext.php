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

namespace Guandeng\Telescope;

use Hyperf\Context\Context;

class TelescopeContext
{
    public const TELESCOPE = 'telescope.telescope';

    public const ROOT = 'telescope.root';

    public const BATCH_ID = 'telescope.batch_id';

    public const SUB_BATCH_ID = 'telescope.sub_batch_id';

    public static function setTracer($tracer)
    {
        return Context::set(self::TELESCOPE, $tracer);
    }

    public static function getRoot()
    {
        return Context::get(self::ROOT) ?: null;
    }

    public static function setBatchId(string $batchId): ?string
    {
        return Context::set(self::BATCH_ID, $batchId);
    }

    public static function getBatchId(): ?string
    {
        return Context::get(self::BATCH_ID) ?: null;
    }

    public static function setSubBatchId(string $batchId): ?string
    {
        return Context::set(self::SUB_BATCH_ID, $batchId);
    }

    public static function getSubBatchId(): ?string
    {
        return Context::get(self::SUB_BATCH_ID) ?: null;
    }
}
