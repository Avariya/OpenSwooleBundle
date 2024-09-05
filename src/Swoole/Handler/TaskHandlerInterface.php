<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole\Handler;

use OpenSwoole\Server;
use OpenSwoole\Server\Task;

interface TaskHandlerInterface
{
    public function handle(Server $server, Task $task): void;
}
