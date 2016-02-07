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

        $client->sendEvent(Argument::withKey('host'));
    }

    public function testDontOverrideHost()
    {
        $client = $this->getRiemannClient();
        $logger = new RiemannLogger($client->reveal());
        $logger->log(['host' => 'foo']);

        $client->sendEvent(Argument::withEntry('host', 'foo'));
    }

    public function testAddTtlIfNotPresent()
    {
        $client = $this->getRiemannClient();
        $logger = new RiemannLogger($client->reveal());
        $logger->log([]);

        $client->sendEvent(Argument::withEntry('ttl', 1));
    }

    public function testDontOverrideTtl()
    {
        $client = $this->getRiemannClient();
        $logger = new RiemannLogger($client->reveal());
        $logger->log(['ttl' => 2611]);

        $client->sendEvent(Argument::withEntry('ttl', 2611));
    }

    public function testSetServiceName()
    {
        $client = $this->getRiemannClient();
        $logger = new RiemannLogger($client->reveal(), 'service');
        $logger->log([]);

        $client->sendEvent(Argument::withEntry('service', 'service'));
    }

    public function testPrependServiceName()
    {
        $serviceName = 'service';
        $client      = $this->getRiemannClient();
        $logger      = new RiemannLogger($client->reveal(), $serviceName);
        $logger->log(['service' => 'foo']);

        $client->sendEvent(Argument::withEntry('service', 'service.foo'));
    }

    public function testOnlyUseServiceFromData()
    {
        $client = $this->getRiemannClient();
        $logger = new RiemannLogger($client->reveal());
        $logger->log(['service' => 'foo']);

        $client->sendEvent(Argument::withEntry('service', 'foo'));
    }

    public function testReformatAttributes()
    {
        $client = $this->getRiemannClient();
        $logger = new RiemannLogger($client->reveal());
        $logger->log([], ['foo' => 'bar']);

        $client->sendEvent(Argument::withEntry('attributes', [['key' => 'foo', 'value' => 'bar']]));
    }

    private function getRiemannClient()
    {
        return $this->prophesize('Trademachines\Riemann\Client');
    }
}
