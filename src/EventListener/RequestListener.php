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
        $this->stopwatch->start('request');
    }

    /**
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $stopwatchEvent = $this->stopwatch->stop('request');
        $duration       = $stopwatchEvent->getDuration();
        $attributes     = $this->flattenAttributes($event->getRequest()->attributes->all());

        $data = [
            'service' => 'request.duration',
            'metrics' => $duration,
        ];

        $this->logger->log($data, $attributes);
    }

    protected function flattenAttributes(array $attributes)
    {
        $flattened = [];

        foreach ($attributes as $key => $value) {
            switch (true) {
                case is_array($value):
                    $flattened[$key] = sprintf('array[length=%s]', count($value));
                    break;
                case is_object($value) && method_exists($value, '__toString'):
                case is_scalar($value):
                case is_string($value):
                case is_bool($value):
                    $flattened[$key] = $value;
                    break;
                default:
                    $type = gettype($value);
                    $info = 'type=' . $type;
                    if (is_object($value)) {
                        $info .= ',class=' . get_class($value);
                    }
                    $flattened[$key] = sprintf('unknown[%s]', $info);
            }
        }

        return $flattened;
    }
}
