<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole\Handler;

use OpenSwoole\Server;

interface TaskHandlerInterface
{
    public function handle(Server $server, int $taskId, int $reactorId, mixed $data): void;
}
