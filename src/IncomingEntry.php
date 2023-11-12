<?php

declare(strict_types=1);
/**
 * This file is part of guandeng/hyperf-telescope.
 *
 * @link     https://github.com/guandeng/hyperf-telescope
 * @document https://github.com/guandeng/hyperf-telescope/blob/main/README.md
 * @contact  guandeng@gmail.com
 */

namespace Guandeng\Telescope;

use Carbon\Carbon;
use Guandeng\Telescope\Model\TelescopeEntryModel;
use Guandeng\Telescope\Model\TelescopeEntryTagModel;
use Hyperf\Stringable\Str;

use function Hyperf\Config\config;

class IncomingEntry
{
    /**
     * The entry's UUID.
     */
    public string $uuid = '';

    /**
     * The entry's batch ID.
     */
    public string $batchId = '';

    /**
     * The entry's sub batch ID.
     */
    public string $subBatchId = '';

    /**
     * The entry's type.
     */
    public string $type = '';

    /**
     * The entry's family hash.
     */
    public ?string $familyHash = null;

    /**
     * The currently authenticated user, if applicable.
     */
    public mixed $user;

    /**
     * The entry's content.
     */
    public array $content = [];

    /**
     * The entry's tags.
     */
    public array $tags = [];

    /**
     * The DateTime that indicates when the entry was recorded.
     */
    public string $recordedAt = '';

    /**
     * Create a new incoming entry instance.
     */
    public function __construct(array $content)
    {
        $this->uuid = (string) Str::orderedUuid()->toString();

        $timezone = config('telescope.timezone') ?: date_default_timezone_get();
        $this->recordedAt = Carbon::now()->setTimezone($timezone)->toDateTimeString();

        $this->content = array_merge($content, ['hostname' => gethostname()]);

        // $this->tags = ['hostname:'.gethostname()];
    }

    /**
     * Create a new entry instance.
     *
     * @param mixed ...$arguments
     */
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }

    /**
     * Assign the entry a given batch ID.
     *
     * @return $this
     */
    public function batchId(string $batchId): static
    {
        $this->batchId = $batchId;

        return $this;
    }

    /**
     * Assign the entry a given sub batch ID.
     *
     * @return $this
     */
    public function subBatchId(string $batchId): static
    {
        $this->subBatchId = $batchId;

        return $this;
    }

    /**
     * Assign the entry a given type.
     *
     * @return $this
     */
    public function type(string $type): static
    {
        $this->type = $type;

        if ($type == EntryType::QUERY && $this->content['slow']) {
            $this->tags(['slow']);
        }

        return $this;
    }

    /**
     * Assign the entry a family hash.
     *
     * @return $this
     */
    public function withFamilyHash(string $familyHash): static
    {
        $this->familyHash = $familyHash;

        return $this;
    }

    /**
     * Set the currently authenticated user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return $this
     */
    public function user($user = null): static
    {
        $authUser = null;
        if (function_exists('auth')) {
            $token = auth()->parseToken();

            if ($token && auth()->check($token)) {
                $authUser = auth()->user();
            }
        }

        $user = $user ?: $authUser;

        if (! is_null($user)) {
            $this->content = array_merge($this->content, [
                'user' => [
                    'id' => $user->getKey(),
                    'name' => $user->name ?? null,
                    'email' => $user->email ?? null,
                ],
            ]);

            $this->tags(['Auth:' . $user->getKey()]);
        }

        $this->user = $user;

        return $this;
    }

    /**
     * Merge tags into the entry's existing tags.
     *
     * @return $this
     */
    public function tags(array $tags): static
    {
        $this->tags = array_unique(array_merge($this->tags, $tags));

        return $this;
    }

    /**
     * Determine if the incoming entry has a monitored tag.
     */
    public function hasMonitoredTag(): bool
    {
        // if (! empty($this->tags)) {
        //     return app(EntriesRepository::class)->isMonitoring($this->tags);
        // }

        return false;
    }

    /**
     * Determine if the incoming entry is a failed request.
     */
    public function isFailedRequest(): bool
    {
        return $this->type === EntryType::REQUEST
            && ($this->content['response_status'] ?? 200) >= 500;
    }

    /**
     * Determine if the incoming entry is a query.
     */
    public function isQuery(): bool
    {
        return $this->type === EntryType::QUERY;
    }

    /**
     * Determine if the incoming entry is a failed job.
     */
    public function isFailedJob(): bool
    {
        return $this->type === EntryType::JOB
            && ($this->content['status'] ?? null) === 'failed';
    }

    /**
     * Determine if the incoming entry is a reportable exception.
     */
    public function isReportableException(): bool
    {
        return false;
    }

    /**
     * Determine if the incoming entry is an exception.
     */
    public function isException(): bool
    {
        return false;
    }

    /**
     * Determine if the incoming entry is a dump.
     */
    public function isDump(): bool
    {
        return false;
    }

    /**
     * Determine if the incoming entry is a scheduled task.
     */
    public function isScheduledTask(): bool
    {
        return $this->type === EntryType::SCHEDULED_TASK;
    }

    /**
     * Get the family look-up hash for the incoming entry.
     */
    public function familyHash(): ?string
    {
        return $this->familyHash;
    }

    /**
     * Get an array representation of the entry for storage.
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'batch_id' => $this->batchId,
            'sub_batch_id' => $this->subBatchId,
            'family_hash' => $this->familyHash,
            'type' => $this->type,
            'content' => $this->content,
            'created_at' => $this->recordedAt,
        ];
    }

    public function create()
    {
        foreach ($this->tags as $tag) {
            $tagItem = [
                'entry_uuid' => $this->uuid,
                'tag' => $tag,
            ];
            TelescopeEntryTagModel::create($tagItem);
        }
        TelescopeEntryModel::create($this->toArray());
    }
}
