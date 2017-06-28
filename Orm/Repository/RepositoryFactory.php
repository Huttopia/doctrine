<?php

declare(strict_types=1);

namespace Huttopia\Doctrine\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Huttopia\Doctrine\Orm\EntityRepository;
use Doctrine\ORM\Repository\RepositoryFactory as InterfaceRepositoryFactory;

class RepositoryFactory implements InterfaceRepositoryFactory
{
    /** @var EntityRepository[] */
    protected $repositories = [];

    /** @var EntityRepository[] */
    protected $repositoryServices = [];

    public function addRepository(string $className, EntityRepository $repository): self
    {
        $this->repositoryServices[$className] = $repository;

        return $this;
    }

    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $repositoryHash = $entityManager->getClassMetadata($entityName)->getName() . spl_object_hash($entityManager);

        $return = (isset($this->repositories[$repositoryHash]))
            ? $this->repositories[$repositoryHash]
            : $this->repositories[$repositoryHash] = $this->createRepository($entityManager, $entityName);

        return $return;
    }

    protected function createRepository(EntityManagerInterface $entityManager, string $entityName): EntityRepository
    {
        $classMetadata = $entityManager->getClassMetadata($entityName);
        if (isset($this->repositoryServices[$classMetadata->getName()])) {
            /** @var EntityRepository $return */
            $return = $this->repositoryServices[$classMetadata->getName()];
        } else {
            $repositoryClassName = $classMetadata->customRepositoryClassName
                ?: $entityManager->getConfiguration()->getDefaultRepositoryClassName();

            /** @var EntityRepository $return */
            $return = new $repositoryClassName();
        }

        return $return
            ->setEntityManager($entityManager)
            ->setClassMetadata($classMetadata);
    }
}
