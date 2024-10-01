<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Event\Server;

use OpenSwoole\Server\Task;
use OpenSwooleServerBundle\Event\OpenSwooleEvent;

final class ServerTaskStarted extends OpenSwooleEvent
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
