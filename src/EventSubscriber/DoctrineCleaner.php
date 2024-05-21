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
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => [
                ['clear', 10],
            ],
        ];
    }

    public function clear(): void
    {
        foreach ($this->registry->getManagers() as $name => $manager) {
            $manager->isOpen()
                ? $manager->clear()
                : $this->registry->resetManager($name);
        }
    }
}
