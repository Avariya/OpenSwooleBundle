<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\DependencyInjection\CompilerPass;

use OpenSwooleServerBundle\EventSubscriber\DoctrineCleaner;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class DoctrineCleanerSubscriberPass
 */
class DoctrineCleanerSubscriberPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach (['doctrine_mongodb', 'doctrine'] as $definitionId) {
            if ($container->hasDefinition($definitionId)) {
                $definition = new Definition(DoctrineCleaner::class);
                $definition->addTag('kernel.event_subscriber');
                $definition->setArguments([new Reference($definitionId)]);
                $container->setDefinition(uniqid('doctrine.clear.listener.'), $definition);
            }
        }
    }
}
