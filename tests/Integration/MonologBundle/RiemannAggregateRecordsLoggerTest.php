<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\Tests\Integration\MonologBundle;

use Prophecy\Argument;
use Trademachines\Bundle\RiemannLoggingBundle\Integration\MonologBundle\RiemannAggregateRecordsLogger;

class RiemannAggregateRecordsLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testDontLogIfNoLevelIsGiven()
    {
        $riemannLogger = $this->getRiemannLogger();
        $logger        = new RiemannAggregateRecordsLogger($riemannLogger->reveal());
        $logger->handle([]);
        $logger->flush();

        $riemannLogger->log(Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testUseLevelAsAttribute()
    {
        $riemannLogger = $this->getRiemannLogger();
        $logger        = new RiemannAggregateRecordsLogger($riemannLogger->reveal());
        $logger->handle(['level_name' => 'foo']);
        $logger->flush();

        $riemannLogger->log(Argument::any(), Argument::withEntry('level', 'foo'))->shouldHaveBeenCalled();
    }

    public function testNormalizeLevel()
    {
        $riemannLogger = $this->getRiemannLogger();
        $logger        = new RiemannAggregateRecordsLogger($riemannLogger->reveal());
        $logger->handle(['level_name' => 'FoO']);
        $logger->flush();

        $riemannLogger->log(Argument::any(), Argument::withEntry('level', 'foo'))->shouldHaveBeenCalled();
    }

    public function testHandleMultipleRecords()
    {
        $riemannLogger = $this->getRiemannLogger();
        $logger        = new RiemannAggregateRecordsLogger($riemannLogger->reveal());
        $logger->handle(['level_name' => 'foo']);
        $logger->handle(['level_name' => 'bar']);
        $logger->handle(['level_name' => 'bar']);
        $logger->flush();

        $riemannLogger->log(
            Argument::withEntry('metrics', 1),
            Argument::withEntry('level', 'foo')
        )->shouldHaveBeenCalled();
        $riemannLogger->log(
            Argument::withEntry('metrics', 2),
            Argument::withEntry('level', 'bar')
        )->shouldHaveBeenCalled();
    }

    private function getRiemannLogger()
    {
        return $this->prophesize('Trademachines\Bundle\RiemannLoggingBundle\RiemannLogger');
    }
}
