<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\EventSubscriber;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listen for the request finish event and clear object manager
 */
class DoctrineCleaner implements EventSubscriberInterface
{
    /**
     * @var ManagerRegistry;
     */
    private $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::FINISH_REQUEST => [
                ['clear', 10],
            ],
        ];
    }

    /**
     * @param FinishRequestEvent $event
     */
    public function clear(FinishRequestEvent $event)
    {
        foreach ($this->registry->getManagers() as $name => $manager) {
            $manager->isOpen()
                ? $manager->clear()
                : $this->registry->resetManager($name);
        }
    }
}
