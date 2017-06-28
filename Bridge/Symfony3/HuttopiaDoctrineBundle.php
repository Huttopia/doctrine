<?php

declare(strict_types=1);

namespace Huttopia\Doctrine\Bridge\Symfony3;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Huttopia\Doctrine\Bridge\Symfony3\DependencyInjection\CompilerPass\RepositoryCompilerPass;

class HuttopiaDoctrineBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RepositoryCompilerPass());
    }
}
