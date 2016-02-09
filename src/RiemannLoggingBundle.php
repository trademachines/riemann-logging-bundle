<?php

namespace Trademachines\Bundle\RiemannLoggingBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RiemannLoggingBundle extends Bundle
{
    /** {@inheritdoc} **/
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
