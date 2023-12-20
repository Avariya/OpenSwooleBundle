<?php
declare(strict_types=1);

namespace OpenSwooleServerBundle;

use OpenSwooleServerBundle\DependencyInjection\CompilerPass\DoctrineCleanerSubscriberPass;
use OpenSwooleServerBundle\DependencyInjection\CompilerPass\SentryPublisherSubscriberPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class OpenSwooleServerBundle
 */
class OpenSwooleServerBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrineCleanerSubscriberPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);
        $container->addCompilerPass(new SentryPublisherSubscriberPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);
    }
}
