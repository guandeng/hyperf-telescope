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

use Hyperf\HttpServer\Request;
use Psr\Http\Message\ServerRequestInterface;

class EntryQueryOptions
{
    /**
     * The batch ID that entries should belong to.
     *
     * @var string
     */
    public $batchId;

    /**
     * The tag that must belong to retrieved entries.
     *
     * @var string
     */
    public $tag;

    /**
     * The family hash that must belong to retrieved entries.
     *
     * @var string
     */
    public $familyHash;

    /**
     * The ID that all retrieved entries should be less than.
     *
     * @var mixed
     */
    public $beforeSequence;

    /**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var mixed
     */
    public $uuids;

    /**
     * The number of entries to retrieve.
     *
     * @var int
     */
    public $limit = 50;

    /**
     * Create new entry query options from the incoming request.
     * @param Request $request
     *
     * @return static
     */
    public static function fromRequest(ServerRequestInterface $request)
    {
        return (new static())
            ->batchId($request->input('batch_id'))
            ->uuids($request->input('uuids'))
            ->beforeSequence($request->input('before'))
            ->tag($request->input('tag'))
            ->familyHash($request->input('family_hash'))
            ->limit($request->input('take') ?? 50);
    }

    /**
     * Create new entry query options for the given batch ID.
     *
     * @return static
     */
    public static function forBatchId(?string $batchId)
    {
        return (new static())->batchId($batchId);
    }

    /**
     * Set the batch ID for the query.
     *
     * @return $this
     */
    public function batchId(?string $batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }

    /**
     * Set the list of UUIDs of entries tor retrieve.
     *
     * @return $this
     */
    public function uuids(?array $uuids)
    {
        $this->uuids = $uuids;

        return $this;
    }

    /**
     * Set the ID that all retrieved entries should be less than.
     *
     * @param mixed $id
     * @return $this
     */
    public function beforeSequence($id)
    {
        $this->beforeSequence = $id;

        return $this;
    }

    /**
     * Set the tag that must belong to retrieved entries.
     *
     * @return $this
     */
    public function tag(?string $tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Set the family hash that must belong to retrieved entries.
     *
     * @return $this
     */
    public function familyHash(?string $familyHash)
    {
        $this->familyHash = $familyHash;

        return $this;
    }

    /**
     * Set the number of entries that should be retrieved.
     *
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }
}
