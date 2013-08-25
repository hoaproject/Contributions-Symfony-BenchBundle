<?php
namespace Hoathis\Bundle\BenchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @param TreeBuilder|null $treeBuilder
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(TreeBuilder $treeBuilder = null)
    {
        $treeBuilder = $treeBuilder ?: new TreeBuilder();
        $rootNode = $treeBuilder->root('xyl');

        return $treeBuilder;
    }
}
