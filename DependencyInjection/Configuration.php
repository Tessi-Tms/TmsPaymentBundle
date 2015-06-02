<?php

/**
 * @author: Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 */

namespace Tms\Bundle\PaymentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tms_payment');

        $rootNode
            ->children()
                ->arrayNode('backends')
                    ->useAttributeAsKey('')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('parameters')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
