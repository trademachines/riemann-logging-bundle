<?php

namespace Trademachines\Bundle\RiemannLoggingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class RiemannLoggingExtension extends Extension
{
    /** {@inheritdoc} **/
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('riemann-logging.yml');

        if (class_exists('Symfony\Bundle\MonologBundle\MonologBundle')) {
            $loader->load('monolog-bundle-integration.yml');
        }
    }
}
