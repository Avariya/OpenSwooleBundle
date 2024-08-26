<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Bridge\Messenger;

use OpenSwooleServerBundle\Swoole\Server;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final class OpenSwooleServerTaskTransportFactory implements TransportFactoryInterface
{
    public function __construct(private readonly Server $server)
    {
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return new OpenSwooleServerTaskTransport($this->server);
    }

    public function supports(string $dsn, array $options): bool
    {
        return mb_strpos($dsn, 'openswoole://server-task') === 0;
    }
}
