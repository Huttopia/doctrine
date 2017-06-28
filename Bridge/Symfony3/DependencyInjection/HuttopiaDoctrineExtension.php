<?php

declare(strict_types=1);

namespace Huttopia\Doctrine\Bridge\Symfony3\DependencyInjection;

use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Loader
};
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class HuttopiaDoctrineExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter(
            'huttopia_doctrine.repository_factory_service',
            $config['repository_factory_service']
        );
    }
}
