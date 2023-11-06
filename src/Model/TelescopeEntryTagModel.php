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

namespace Guandeng\Telescope\Model;

use Hyperf\DbConnection\Model\Model;

class TelescopeEntryTagModel extends Model
{
    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected ?string $table = 'telescope_entries_tags';

    protected ?string $connection = 'telescope';

    protected array $fillable = [
        'entry_uuid',
        'tag',
    ];
}
