<?php

declare(strict_types=1);
/**
 * This file is part of guandeng/hyperf-telescope.
 *
 * @link     https://github.com/guandeng/hyperf-telescope
 * @document https://github.com/guandeng/hyperf-telescope/blob/main/README.md
 * @contact  guandeng@gmail.com
 */

namespace Guandeng\Telescope\Controller;

use Guandeng\Telescope\EntryType;
use Guandeng\Telescope\Model\TelescopeEntryModel;
use Guandeng\Telescope\Model\TelescopeEntryTagModel;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class EntryController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    public function index()
    {
        $before = $this->request->input('before');
        $limit = $this->request->input('take', 50);
        $tag = $this->request->input('tag');
        $query = (new TelescopeEntryModel())->setTelescopeConnection()->where('type', $this->entryType())->orderByDesc('sequence');

        if ($before) {
            $query->where('sequence', '<', $before);
        }

        if ($tag) {
            $query->join('telescope_entries_tags', 'telescope_entries_tags.entry_uuid', '=', 'telescope_entries.uuid')->where('tag', $tag);
        }

        $entries = $query->limit($limit)->get()->toArray();

        foreach ($entries as &$item) {
            if (isset($item['content']['response'])) {
                $item['content']['response'] = '';
            }
        }

        return $this->response->json([
            'entries' => $entries,
            'status' => $this->status(),
        ]);
    }

    public function show($id)
    {
        $entry = (new TelescopeEntryModel())->setTelescopeConnection()->find($id);
        $entry->tags = (new TelescopeEntryTagModel())->setTelescopeConnection()->where('entry_uuid', $id)->pluck('tag')->toArray();

        $query = (new TelescopeEntryModel())->setTelescopeConnection()->where('batch_id', $entry->batch_id);
        if ($this->entryType() == EntryType::SERVICE) {
            $query->where('sub_batch_id', $entry->sub_batch_id);
        }

        $batch = $query->orderByDesc('sequence')->get();

        return $this->response->json([
            'entry' => $entry,
            'batch' => $batch,
        ]);
    }

    /**
     * The entry type for the controller.
     *
     * @return string
     */
    abstract protected function entryType();

    /**
     * The watcher class for the controller.
     *
     * @return string
     */
    abstract protected function watcher();

    /**
     * Determine the watcher recording status.
     *
     * @return string
     */
    protected function status()
    {
        // if (! config('telescope.enabled', false)) {
        //     return 'disabled';
        // }

        // if (cache('telescope:pause-recording', false)) {
        //     return 'paused';
        // }

        // $watcher = config('telescope.watchers.'.$this->watcher());

        // if (! $watcher || (isset($watcher['enabled']) && ! $watcher['enabled'])) {
        //     return 'off';
        // }

        return 'enabled';
    }
}
