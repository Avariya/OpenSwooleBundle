<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\EventSubscriber;

use PhpAmqpLib\Exception\AMQPExceptionInterface;
use OpenSwooleServerBundle\Swoole\Server;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * ExceptionSubscriber
 */
class AMQPReconnectSubscriber implements EventSubscriberInterface
{
    /**
     * @var Server
     */
    private $server;

    /**
     * ExceptionSubscriber constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 255],
        ];
    }

    /**
     * @param ExceptionEvent $event
     *
     * @throws \Exception
     */
    public function onKernelException(ExceptionEvent $event)
    {
        if ($event->getThrowable() instanceof AMQPExceptionInterface) {
            $this->server->stopWorker();
        }
    }
}
