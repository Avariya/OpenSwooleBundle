<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole\Handler;

use OpenSwoole\Server;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerSendTaskHandler implements TaskHandlerInterface
{
    public function __construct(
        private MessageBusInterface $messenger,
    ) {
    }

    public function handle(Server $server, int $taskId, int $reactorId, mixed $data): void
    {
        $this->messenger->dispatch($data);
    }
}
