<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole\Handler;

use OpenSwoole\Server;
use OpenSwoole\Server\Task;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final class MessengerSendTaskHandler implements TaskHandlerInterface
{
    public function __construct(
        private MessageBusInterface $messenger,
    ) {
    }

    public function handle(Server $server, Task $task): void
    {
        try {
            $this->messenger->dispatch($task->data);
        } catch (Throwable $e) {
            error_log(sprintf("Failed to dispatch task #%d.\nReason: %s.\nTrace: %s.", $task->id, $e->getMessage(), $e->getTraceAsString()));
        }
    }
}
