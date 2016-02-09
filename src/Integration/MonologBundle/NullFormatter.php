<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\Integration\MonologBundle;

use Monolog\Formatter\FormatterInterface;

class NullFormatter implements FormatterInterface
{
    /** {@inheritdoc} **/
    public function format(array $record)
    {
        return $record;
    }

    /** {@inheritdoc} **/
    public function formatBatch(array $records)
    {
        return $records;
    }
}
