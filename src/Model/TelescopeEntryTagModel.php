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

use Hyperf\DbConnection\Model\Model;

use function Hyperf\Config\config;

class TelescopeEntryTagModel extends Model
{
    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected ?string $table = 'telescope_entries_tags';

    protected array $fillable = [
        'entry_uuid',
        'tag',
    ];
}
