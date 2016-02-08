<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\Decorator;

use Psr\Log\LoggerInterface;
use Trademachines\Bundle\RiemannLoggingBundle\RiemannLogger;

class PsrLoggerDecorator implements LoggerInterface
{
    /** @var RiemannLogger */
    protected $riemannLogger;

    /** @var LoggerInterface */
    protected $decoratedLogger;

    /** @var array */
    protected $logCount = [];

    /**
     * PsrLoggerDecorator constructor.
     *
     * @param RiemannLogger   $riemannLogger
     * @param LoggerInterface $decoratedLogger
     */
    public function __construct(RiemannLogger $riemannLogger, LoggerInterface $decoratedLogger)
    {
        $this->riemannLogger   = $riemannLogger;
        $this->decoratedLogger = $decoratedLogger;
    }

    /** {@inheritdoc} **/
    public function emergency($message, array $context = array())
    {
        $this->delegate(__FUNCTION__, func_get_args());
    }

    /** {@inheritdoc} **/
    public function alert($message, array $context = array())
    {
        $this->delegate(__FUNCTION__, func_get_args());
    }

    /** {@inheritdoc} **/
    public function critical($message, array $context = array())
    {
        $this->delegate(__FUNCTION__, func_get_args());
    }

    /** {@inheritdoc} **/
    public function error($message, array $context = array())
    {
        $this->delegate(__FUNCTION__, func_get_args());
    }

    /** {@inheritdoc} **/
    public function warning($message, array $context = array())
    {
        $this->delegate(__FUNCTION__, func_get_args());
    }

    /** {@inheritdoc} **/
    public function notice($message, array $context = array())
    {
        $this->delegate(__FUNCTION__, func_get_args());
    }

    /** {@inheritdoc} **/
    public function info($message, array $context = array())
    {
        $this->delegate(__FUNCTION__, func_get_args());
    }

    /** {@inheritdoc} **/
    public function debug($message, array $context = array())
    {
        $this->delegate(__FUNCTION__, func_get_args());
    }

    /** {@inheritdoc} **/
    public function log($level, $message, array $context = array())
    {
        $this->delegate(__FUNCTION__, func_get_args());
    }

    public function __destruct()
    {
        foreach ($this->logCount as $level => $count) {
            $this->riemannLogger->log(['service' => 'logs.count', 'metrics' => $count], ['level' => $level]);
        }
    }

    protected function addMessage($level)
    {
        if (!isset($this->logCount[$level])) {
            $this->logCount[$level] = 0;
        }

        $this->logCount[$level]++;
    }

    private function delegate($method, array $arguments)
    {
        call_user_func_array([$this->decoratedLogger, $method], $arguments);
        $level = $method;

        if ($method === 'log' && count($arguments) > 0) {
            $level = $arguments[0];
        }

        $this->addMessage($level);
    }

    public function getDecoratedLogger()
    {
        return $this->decoratedLogger;
    }
}
