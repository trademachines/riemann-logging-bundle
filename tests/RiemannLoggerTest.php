<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\Tests;

use Prophecy\Argument;
use Trademachines\Bundle\RiemannLoggingBundle\RiemannLogger;

class RiemannLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testAddHostIfNotPresent()
    {
        $client = $this->getRiemannClient();
        $logger = new RiemannLogger($client->reveal());
        $logger->log([]);

        $client->sendEvent(Argument::withKey('host'))->shouldHaveBeenCalled();
    }

    public function testDontOverrideHost()
    {
        $client = $this->getRiemannClient();
        $logger = new RiemannLogger($client->reveal());
        $logger->log(['host' => 'foo']);

        $client->sendEvent(Argument::withEntry('host', 'foo'))->shouldHaveBeenCalled();
    }

    public function testSetServiceName()
    {
        $client = $this->getRiemannClient();
        $logger = new RiemannLogger($client->reveal(), 'service');
        $logger->log([]);

        $client->sendEvent(Argument::withEntry('service', 'service'))->shouldHaveBeenCalled();
    }

    public function testUseNameAsAttributeIfServiceAndIdentIsGiven()
    {
        $serviceName    = 'some-service';
        $identAttribute = 'source';
        $client         = $this->getRiemannClient();
        $logger         = new RiemannLogger($client->reveal(), $serviceName, $identAttribute);
        $logger->log(['service' => 'foo']);

        $client->sendEvent(
            Argument::allOf(
                Argument::withEntry('service', 'foo'),
                Argument::withEntry('attributes', [['key' => $identAttribute, 'value' => $serviceName]])
            )
        )->shouldHaveBeenCalled();
    }

    public function testDontOverrideServiceName()
    {
        $serviceName = 'service';
        $client      = $this->getRiemannClient();
        $logger      = new RiemannLogger($client->reveal(), $serviceName);
        $logger->log(['service' => 'foo']);

        $client->sendEvent(Argument::withEntry('service', 'foo'))->shouldHaveBeenCalled();
    }

    public function testReformatAttributes()
    {
        $client = $this->getRiemannClient();
        $logger = new RiemannLogger($client->reveal());
        $logger->log([], ['foo' => 'bar']);

        $client->sendEvent(
            Argument::withEntry('attributes', [['key' => 'foo', 'value' => 'bar']])
        )->shouldHaveBeenCalled();
    }

    private function getRiemannClient()
    {
        return $this->prophesize('Trademachines\Riemann\Client');
    }
}
