<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole\Handler;

use OpenSwoole\Server;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final class MessengerSendTaskHandler implements TaskHandlerInterface
{
    public function __construct(
        private MessageBusInterface $messenger,
    ) {
    }

    public function handle(Server $server, int $taskId, int $reactorId, mixed $data): void
    {
        try {
            $this->messenger->dispatch($data);
        } catch (Throwable $e) {
            error_log(sprintf("Failed to dispatch task #%d.\nReason: %s.\nTrace: %s.", $taskId, $e->getMessage(), $e->getTraceAsString()));
        }
    }
}
