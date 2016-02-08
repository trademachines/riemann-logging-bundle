<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\Tests\Decorator;

use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Trademachines\Bundle\RiemannLoggingBundle\Decorator\PsrLoggerDecorator;

class PsrLoggerDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideInterfaceMethods
     */
    public function testDelegateInterfaceMethods($method, array $arguments)
    {
        $riemannLogger = $this->getRiemannLogger();
        $logger        = $this->getLogger();

        $loggerDecorator = new PsrLoggerDecorator($riemannLogger->reveal(), $logger->reveal());
        call_user_func_array([$loggerDecorator, $method], $arguments);

        $prophecy = new MethodProphecy($logger, $method, $arguments);
        $prophecy->shouldHaveBeenCalled();
    }

    /**
     * @dataProvider provideInterfaceMethods
     */
    public function testCountMessages($method, array $arguments, $level)
    {
        $riemannLogger = $this->getRiemannLogger();
        $logger        = $this->getLogger();

        $loggerDecorator = new PsrLoggerDecorator($riemannLogger->reveal(), $logger->reveal());
        call_user_func_array([$loggerDecorator, $method], $arguments);

        $loggerDecorator->__destruct();

        $riemannLogger->log(Argument::withEntry('metrics', 1), ['level' => $level])->shouldHaveBeenCalled();
    }

    public function provideInterfaceMethods()
    {
        $class = new \ReflectionClass('Psr\Log\LoggerInterface');

        foreach ($class->getMethods() as $method) {
            $method = $method->getName();
            $level  = $method;

            if ('log' === $method) {
                $arguments = ['some-level', 'message', ['foo' => 'bar']];
                $level     = 'some-level';
            } else {
                $arguments = ['message', ['foo' => 'bar']];
            }

            yield [$method, $arguments, $level];
        }
    }

    private function getRiemannLogger()
    {
        return $this->prophesize('Trademachines\Bundle\RiemannLoggingBundle\RiemannLogger');
    }

    private function getLogger()
    {
        return $this->prophesize('Psr\Log\LoggerInterface');
    }
}
