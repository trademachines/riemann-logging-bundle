<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\Tests\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Trademachines\Bundle\RiemannLoggingBundle\DependencyInjection\Compiler\DecorateServicesPass;

class DecorateServicesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testNoLoggerDefinedDoesNotDecorate()
    {
        $container = new ContainerBuilder();
        $pass      = new DecorateServicesPass();
        $pass->process($container);

        self::assertFalse(
            $container->hasDefinition('riemann.decorated.logger'),
            'Container should not have a decorated logger'
        );
    }

    public function testLoggerDefinitionWithoutClassDoesNotDecorate()
    {
        $container = new ContainerBuilder();
        $container->register('logger');
        $pass = new DecorateServicesPass();
        $pass->process($container);

        self::assertFalse(
            $container->hasDefinition('riemann.decorated.logger'),
            'Container should not have a decorated logger'
        );
    }

    public function testNonPsrLoggerDefinitionDoesNotDecorate()
    {
        $container = new ContainerBuilder();
        $container->register('logger', 'stdClass');
        $pass = new DecorateServicesPass();
        $pass->process($container);

        self::assertFalse(
            $container->hasDefinition('riemann.decorated.logger'),
            'Container should not have a decorated logger'
        );
    }

    public function testPsrLoggerGetsDecorated()
    {
        $decoratorClass = 'Trademachines\Bundle\RiemannLoggingBundle\Decorator\PsrLoggerDecorator';
        $container      = new ContainerBuilder();
        $container->setParameter('riemann.decorate.logger', true);
        $container->setParameter('riemann.decorate.logger.class', $decoratorClass);
        $container->register('logger', $decoratorClass);
        $pass = new DecorateServicesPass();
        $pass->process($container);

        self::assertTrue(
            $container->hasDefinition('riemann.decorated.logger'),
            'Container should have a decorated logger'
        );
        $decoratedService = $container->getDefinition('riemann.decorated.logger')->getDecoratedService();

        self::assertInternalType('array', $decoratedService);
        list($decoratedService, $renameId, $priority) = $decoratedService;
        self::assertEquals('logger', $decoratedService);
    }
}
