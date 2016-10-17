<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\Integration\MonologBundle;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Trademachines\RiemannLogger\RiemannLoggerInterface;

class RiemannAggregateRecordsLogger implements HandlerInterface
{
    /** @var RiemannLoggerInterface */
    protected $riemannLogger;

    /** @var array */
    protected $logCount = [];

    /** @var FormatterInterface */
    private $formatter;

    public function __construct(RiemannLoggerInterface $riemannLogger)
    {
        $this->riemannLogger = $riemannLogger;
        $this->formatter     = new NullFormatter();
    }

    /** {@inheritdoc} **/
    public function isHandling(array $record)
    {
        return true;
    }

    /** {@inheritdoc} **/
    public function handle(array $record)
    {
        $level = isset($record['level_name']) ? strtolower($record['level_name']) : null;

        if ($level) {
            if (!isset($this->logCount[$level])) {
                $this->logCount[$level] = 0;
            }

            $this->logCount[$level]++;
        }

        return false;
    }

    /**
     * Handles a set of records at once.
     *
     * @param array $records The records to handle (an array of record arrays)
     */
    public function handleBatch(array $records)
    {
        array_map([$this, 'handle'], $records);
    }

    /** {@inheritdoc} **/
    public function pushProcessor($callback)
    {
        return $this;
    }

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function popProcessor()
    {
        return null;
    }

    /**
     * Sets the formatter.
     *
     * @param  FormatterInterface $formatter
     * @return self
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        return $this;
    }

    /**
     * Gets the formatter.
     *
     * @return FormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $this->flush();
    }

    public function flush()
    {
        foreach ($this->logCount as $level => $count) {
            $this->riemannLogger->log(['service' => 'logs.count', 'metrics' => $count], ['level' => $level]);
        }
    }
}
