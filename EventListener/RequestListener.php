<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\EventListener;

use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\Stopwatch\Stopwatch;
use Trademachines\Bundle\RiemannLoggingBundle\RiemannLogger;

class RequestListener
{
    /** @var RiemannLogger */
    protected $logger;

    /** @var Stopwatch */
    protected $stopwatch;

    /**
     * RequestListener constructor.
     *
     * @param RiemannLogger  $logger
     * @param Stopwatch|null $stopwatch
     */
    public function __construct(RiemannLogger $logger, Stopwatch $stopwatch = null)
    {
        $this->logger    = $logger;
        $this->stopwatch = $stopwatch ?: new Stopwatch();
    }

    /**
     * @param KernelEvent $event
     */
    public function onKernelRequest(KernelEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->stopwatch->start('request');
    }

    /**
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $stopwatchEvent = $this->stopwatch->stop('request');
        $duration       = $stopwatchEvent->getDuration();
        $attributes     = $event->getRequest()->attributes->all();

        $data = [
            'service' => 'request.duration',
            'metrics' => $duration,
        ];

        $this->logger->log($data, $attributes);
    }
}
