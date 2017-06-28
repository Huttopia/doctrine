<?php

declare(strict_types=1);

namespace Huttopia\Doctrine\Bridge\Symfony3\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\{
    Compiler\CompilerPassInterface,
    ContainerBuilder,
    Reference
};

class RepositoryCompilerPass implements CompilerPassInterface
{
    public const DEFAULT_REPOSITORY_FACTORY = 'huttopia.doctrine.repository_factory';

    public function process(ContainerBuilder $container)
    {
        $serviceId = $container->getParameter('huttopia_doctrine.repository_factory_service');
        $repositoryFactory = $container->getDefinition($serviceId);
        foreach ($container->findTaggedServiceIds('huttopia.repository') as $id => $data) {
            $repositoryFactory->addMethodCall('addRepository', [$data[0]['entity'], new Reference($id)]);
        }
        $container->setDefinition($serviceId, $repositoryFactory);

        if ($serviceId !== static::DEFAULT_REPOSITORY_FACTORY) {
            $container->removeDefinition(static::DEFAULT_REPOSITORY_FACTORY);
        }
    }
}
