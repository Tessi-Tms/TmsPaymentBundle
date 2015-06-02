<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\StepBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class BackendCompilerPath implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('tms_payment.backend_registry')) {
            return;
        }
        $registryDefinition = $container->getDefinition('tms_payment.backend_registry');
        foreach ($container->findTaggedServiceIds('tms_payment.backend') as $id => $tags) {
            foreach ($tags as $attributes) {
                $alias = isset($attributes['alias'])
                    ? $attributes['alias']
                    : $id
                ;
                $registryDefinition->addMethodCall(
                    'setBackend',
                    array($alias, new Reference($id))
                );
            }
        }
    }
}