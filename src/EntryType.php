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

class EntryType
{
    public const CACHE = 'cache';

    public const COMMAND = 'command';

    public const DUMP = 'dump';

    public const EVENT = 'event';

    public const EXCEPTION = 'exception';

    public const JOB = 'job';

    public const LOG = 'log';

    public const MAIL = 'mail';

    public const MODEL = 'model';

    public const NOTIFICATION = 'notification';

    public const QUERY = 'query';

    public const REDIS = 'redis';

    public const REQUEST = 'request';

    public const SCHEDULED_TASK = 'schedule';

    public const GATE = 'gate';

    public const VIEW = 'view';

    public const SERVICE = 'service';

    public const CLIENT_REQUEST = 'client_request';
}
