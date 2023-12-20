<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{
    private const DEFAULT_BUFFER_OUTPUT_SIZE = 8388608;

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('swoole_server');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('host')->defaultValue('0.0.0.0')->end()
                ->integerNode('port')->defaultValue(80)->end()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('pid_file')
                            ->cannotBeEmpty()
                            ->defaultValue(getenv('HOME') . '/openswoole_server.pid')
                        ->end()
                        ->scalarNode('log_file')
                            ->cannotBeEmpty()
                            ->defaultValue('/proc/self/fd/1')
                        ->end()
                        ->scalarNode('log_level')
                            ->cannotBeEmpty()
                            ->defaultValue(5)
                        ->end()
                        ->booleanNode('daemonize')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('document_root')
                            ->cannotBeEmpty()
                            ->defaultValue('%kernel.project_dir%/public')
                        ->end()
                        ->booleanNode('enable_static_handler')
                            ->defaultTrue()
                        ->end()
                        ->variableNode('max_request')
                            ->defaultValue(0)
                        ->end()
                        ->variableNode('max_request_grace')
                            ->defaultValue(0)
                        ->end()
                        ->variableNode('open_cpu_affinity')->end()
                        ->variableNode('enable_reuse_port')->end()
                        ->variableNode('open_http2_protocol')
                            ->defaultFalse()
                        ->end()
                        ->variableNode('dispatch_mode')
                            ->defaultValue(2)
                        ->end()
                        ->variableNode('worker_num')
                            ->defaultValue(4)
                        ->end()
                        ->variableNode('reactor_num')
                            ->defaultValue(8)
                        ->end()
                        ->variableNode('buffer_output_size')
                            ->defaultValue(self::DEFAULT_BUFFER_OUTPUT_SIZE)
                        ->end()
                        ->variableNode('user')->end()
                        ->variableNode('group')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
