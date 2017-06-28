<?php

declare(strict_types=1);

namespace Huttopia\Doctrine\Bridge\Symfony3\DependencyInjection;

use Huttopia\Doctrine\Bridge\Symfony3\DependencyInjection\CompilerPass\RepositoryCompilerPass;
use Symfony\Component\Config\{
    Definition\Builder\TreeBuilder,
    Definition\ConfigurationInterface
};

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('huttopia_doctrine');

        $rootNode
            ->children()
                ->scalarNode('repository_factory_service')
                    ->defaultValue(RepositoryCompilerPass::DEFAULT_REPOSITORY_FACTORY)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
