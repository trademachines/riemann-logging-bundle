<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\Tests\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Trademachines\Bundle\RiemannLoggingBundle\DependencyInjection\Compiler\MonologIntegrationPass;

class MonologIntegrationPassTest extends \PHPUnit_Framework_TestCase
{
    public function testDontAddHandlerIfIntegrationIsNotEnabled()
    {
        $definition = new Definition();
        $container  = $this->getContainerBuilder(
            [
                'monolog.logger_prototype' => $definition,
            ]
        );
        $pass       = new MonologIntegrationPass();
        $pass->process($container);

        self::assertCount(0, $definition->getMethodCalls());
    }

    public function testAddMethodCallToMonolog()
    {
        $definition = new Definition();
        $container  = $this->getContainerBuilder(
            [
                'monolog.logger_prototype' => $definition,
            ],
            [
                'riemann.integration.monolog' => true,
            ]
        );
        $pass       = new MonologIntegrationPass();
        $pass->process($container);

        self::assertCount(1, $definition->getMethodCalls());
    }

    private function getContainerBuilder(array $definitions, array $parameters = [])
    {
        $builder = new ContainerBuilder();

        foreach ($definitions as $id => $definition) {
            $builder->setDefinition($id, $definition);
        }
        foreach ($parameters as $key => $value) {
            $builder->setParameter($key, $value);
        }

        return $builder;
    }
}
