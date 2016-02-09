<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MonologIntegrationPass implements CompilerPassInterface
{
    /** {@inheritdoc} **/
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('monolog.logger_prototype')) {
            return;
        }

        if (!$this->isIntegrationEnabled($container)) {
            return;
        }

        $definition = $container->getDefinition('monolog.logger_prototype');
        $definition->addMethodCall('pushHandler', [new Reference('riemann.integration.monolog.logger')]);
    }

    private function isIntegrationEnabled(ContainerBuilder $container)
    {
        if (!$container->hasParameter('riemann.integration.monolog')) {
            return false;
        }

        return true === $container->getParameter('riemann.integration.monolog');
    }
}
