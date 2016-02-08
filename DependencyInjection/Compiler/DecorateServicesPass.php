<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DecorateServicesPass implements CompilerPassInterface
{
    /** {@inheritdoc} **/
    public function process(ContainerBuilder $container)
    {
        $this->decorateLogger($container);
    }

    private function isPsrLogger(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('logger')) {
            return false;
        }

        if (!$class = $container->getDefinition('logger')->getClass()) {
            return false;
        }

        return is_subclass_of($class, 'Psr\Log\LoggerInterface');
    }

    private function doDecorateLogger(ContainerBuilder $container)
    {
        $decorateLoggerClass = $container->getParameter('riemann.decorate.logger.class');
        $definition          = $container->register('riemann.decorated.logger', $decorateLoggerClass);
        $definition->setDecoratedService('logger');
        $definition->addArgument(new Reference('logger.inner'));
        $definition->addArgument(new Reference('riemann.logger'));
        $definition->setPublic(false);
    }

    private function decorateLogger(ContainerBuilder $container)
    {
        $parameter   = 'riemann.decorate.logger';
        $decorateIt  = $container->hasParameter($parameter) && true === $container->getParameter($parameter);
        $isPsrLogger = $this->isPsrLogger($container);

        if ($decorateIt && $isPsrLogger) {
            $this->doDecorateLogger($container);
        }
    }
}
