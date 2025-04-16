<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Tests;

use OpenSwooleServerBundle\DependencyInjection\CompilerPass\DoctrineCleanerSubscriberPass;
use OpenSwooleServerBundle\DependencyInjection\CompilerPass\LoggerCompilerPass;
use OpenSwooleServerBundle\DependencyInjection\CompilerPass\SentryPublisherSubscriberPass;
use OpenSwooleServerBundle\OpenSwooleServerBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpenSwooleServerBundleTest extends TestCase
{
    public function testBuild()
    {
        $containerBuilder = new ContainerBuilder();
        $bundle = new OpenSwooleServerBundle();
        $bundle->build($containerBuilder);
        $passes = $containerBuilder->getCompiler()->getPassConfig()->getBeforeOptimizationPasses();

        $this->assertContains(
            DoctrineCleanerSubscriberPass::class,
            array_map(static fn (CompilerPassInterface $pass) => $pass::class, $passes),
        );
        $this->assertContains(
            SentryPublisherSubscriberPass::class,
            array_map(static fn (CompilerPassInterface $pass) => $pass::class, $passes),
        );
        $this->assertContains(
            LoggerCompilerPass::class,
            array_map(static fn (CompilerPassInterface $pass) => $pass::class, $passes),
        );
    }
}
