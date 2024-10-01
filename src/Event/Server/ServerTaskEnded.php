<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Event\Server;

use OpenSwooleServerBundle\Event\OpenSwooleEvent;
use OpenSwoole\Server\Task;

final class ServerTaskEnded extends OpenSwooleEvent
{
    public function __construct(
        private Task $task,
    ) {
    }

    public function getTask(): Task
    {
        return $this->task;
    }
}
