<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\EventSubscriber;

use Sentry\ClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SentryPublisherSubscriber implements EventSubscriberInterface
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::FINISH_REQUEST => [
                ['flush', 10],
            ],
        ];
    }

    public function flush(): void
    {
        $this->client->flush();
    }
}
