<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole\Handler;

use OpenSwoole\Server;

interface TaskFinishHandlerInterface
{
    public function handle(Server $server, int $taskId, mixed $data): void;
}
