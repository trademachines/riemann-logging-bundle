<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\Tests\EventListener;

use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Tests\Fixtures\KernelForTest;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Trademachines\Bundle\RiemannLoggingBundle\EventListener\RequestListener;

class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testDontStartStopwatchForNonMasterRequest()
    {
        $logger    = $this->getRiemannLogger();
        $stopwatch = $this->getStopwatch();
        $listener  = new RequestListener($logger->reveal(), $stopwatch->reveal());
        $event     = $this->getKernelEvent(HttpKernelInterface::SUB_REQUEST);

        $listener->onKernelRequest($event);

        $stopwatch->start(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testStartStopwatchForMasterRequest()
    {
        $logger    = $this->getRiemannLogger();
        $stopwatch = $this->getStopwatch();
        $listener  = new RequestListener($logger->reveal(), $stopwatch->reveal());
        $event     = $this->getKernelEvent(HttpKernelInterface::MASTER_REQUEST);

        $listener->onKernelRequest($event);

        $stopwatch->start(Argument::any())->shouldHaveBeenCalled();
    }

    public function testAddDurationToData()
    {
        $duration  = 2611;
        $logger    = $this->getRiemannLogger();
        $stopwatch = $this->getStopwatch();
        $listener  = new RequestListener($logger->reveal(), $stopwatch->reveal());
        $event     = $this->getKernelEvent();

        $stopwatch->stop(Argument::any())->will(
            function () use ($duration) {
                return new FixedDurationStopwatchEvent($duration);
            }
        );
        $listener->onKernelTerminate($event);

        $logger->log(Argument::withEntry('metrics', $duration), Argument::any())->shouldHaveBeenCalled();
    }

    public function testUseRequestAttributes()
    {
        $attributes = [
            'foo' => 'bar',
        ];
        $logger     = $this->getRiemannLogger();
        $listener   = new RequestListener($logger->reveal());
        $event      = $this->getKernelEvent();
        $event->getRequest()->attributes->replace($attributes);

        $listener->onKernelRequest($event);
        $listener->onKernelTerminate($event);

        $logger->log(Argument::any(), $attributes)->shouldHaveBeenCalled();
    }

    private function getRiemannLogger()
    {
        return $this->prophesize('Trademachines\Bundle\RiemannLoggingBundle\RiemannLogger');
    }

    private function getStopwatch()
    {
        return $this->prophesize('Symfony\Component\Stopwatch\Stopwatch');
    }

    private function getKernelEvent($requestType = HttpKernelInterface::MASTER_REQUEST)
    {
        return new KernelEvent(new KernelForTest('', true), Request::create('/'), $requestType);
    }
}

class FixedDurationStopwatchEvent extends StopwatchEvent
{
    private $duration;

    public function __construct($duration)
    {
        parent::__construct(0);
        $this->duration = $duration;
    }

    public function getDuration()
    {
        return $this->duration;
    }
}
