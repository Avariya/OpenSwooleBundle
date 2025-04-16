<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Tests\TestCase\EventSubscriber;

use OpenSwooleServerBundle\EventSubscriber\SentryPublisherSubscriber;
use OpenSwooleServerBundle\Tests\Kernel;
use PHPUnit\Framework\TestCase;
use Sentry\ClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class SentryPublisherSubscriberTest extends TestCase
{
    public function testFlush(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('flush');

        $subscriber = new SentryPublisherSubscriber($client);

        $event = new TerminateEvent(
            new Kernel(),
            new Request(),
            new Response(),
        );

        $subscriber->flush($event);
    }
}
