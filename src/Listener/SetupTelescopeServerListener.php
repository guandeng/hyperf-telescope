<?php

declare(strict_types=1);
/**
 * This file is part of guandeng/hyperf-telescope.
 *
 * @link     https://github.com/guandeng/hyperf-telescope
 * @document https://github.com/guandeng/hyperf-telescope/blob/main/README.md
 * @contact  guandeng@gmail.com
 */

namespace Guandeng\Telescope\Listener;

use Guandeng\Telescope\Server\Server;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Server\Event;
use Hyperf\Server\ServerInterface;

class SetupTelescopeServerListener implements ListenerInterface
{
    public function __construct(private ConfigInterface $config)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        if (! $this->config->get('telescope.server.enable', false)) {
            return;
        }

        $host = $this->config->get('telescope.server.host', '0.0.0.0');
        $port = (int) $this->config->get('telescope.server.port', 9509);
        $servers = $this->config->get('server.servers');

        $servers[] = [
            'name' => 'telescope',
            'type' => ServerInterface::SERVER_HTTP,
            'host' => $host,
            'port' => $port,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Server::class, 'onRequest'],
            ],
        ];

        $this->config->set('server.servers', $servers);
    }
}
