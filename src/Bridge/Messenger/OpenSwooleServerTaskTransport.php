<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Bridge\Messenger;

use OpenSwooleServerBundle\Swoole\Server;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;
use Symfony\Component\Messenger\Transport\TransportInterface;

final readonly class OpenSwooleServerTaskTransport implements TransportInterface
{
    private const DEFAULT_TRANSPORT_NAME = 'openswoole';

    public function __construct(
        private Server $server,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function send(Envelope $envelope): Envelope
    {
        /** @var SentStamp|null $sentStamp */
        $sentStamp = $envelope->last(SentStamp::class);
        $alias = $sentStamp === null ? self::DEFAULT_TRANSPORT_NAME : $sentStamp->getSenderAlias() ?? $sentStamp->getSenderClass();

        $envelope = $envelope->with(new ReceivedStamp($alias));

        $taskId = $this->server->task($envelope);

        if ($taskId === null) {
            return $this->messageBus->dispatch($envelope);
        }

        return $envelope;
    }

    public function get(): iterable
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function ack(Envelope $envelope): void
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function reject(Envelope $envelope): void
    {
        throw new \BadMethodCallException('Not implemented');
    }
}
