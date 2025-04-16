<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\DependencyInjection;

use OpenSwooleServerBundle\Swoole\Handler\TaskFinishHandlerInterface;
use OpenSwooleServerBundle\Swoole\Handler\TaskHandlerInterface;
use OpenSwooleServerBundle\Swoole\Server;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class OpenSwooleServerExtension.
 */
class OpenSwooleServerExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition(Server::class);
        $definition->replaceArgument(0, $config['host']);
        $definition->replaceArgument(1, $config['port']);
        $definition->replaceArgument(2, $config['options']);
        $definition->replaceArgument(3, $config['hook_flags']);
        $definition->replaceArgument(6, $config['use_sync_worker']);

        if ($config['use_server_task_messenger']) {
            $definition->replaceArgument(9, new Reference(TaskHandlerInterface::class));
            $definition->replaceArgument(10, new Reference(TaskFinishHandlerInterface::class));
        }
    }
}
