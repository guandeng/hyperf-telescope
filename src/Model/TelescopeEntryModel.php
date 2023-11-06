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

class TelescopeEntryModel extends Model
{
    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    public const UPDATED_AT = null;

    /**
     * Prevent Eloquent from overriding uuid with `lastInsertId`.
     */
    public bool $incrementing = false;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'telescope_entries';

    /**
     * connection name.
     */
    protected ?string $connection = 'telescope';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'sequence',
        'uuid',
        'batch_id',
        'sub_batch_id',
        'family_hash',
        'should_display_on_index',
        'type',
        'content',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'content' => 'json',
    ];

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'uuid';

    /**
     * The "type" of the auto-incrementing ID.
     */
    protected string $keyType = 'string';

    protected array $appends = ['id'];

    public function getIdAttribute()
    {
        return $this->uuid;
    }
}
