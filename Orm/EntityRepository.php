<?php

declare(strict_types=1);

namespace Huttopia\Doctrine\Orm;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\{
    EntityManagerInterface,
    EntityNotFoundException,
    Mapping\ClassMetadata,
    NativeQuery,
    Query,
    Query\ResultSetMappingBuilder,
    QueryBuilder
};
use steevanb\DoctrineReadOnlyHydrator\Hydrator\ReadOnlyHydrator;

class EntityRepository implements ObjectRepository
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var ClassMetadata */
    protected $classMetadata;

    public function setEntityManager(EntityManagerInterface $entityManager): self
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function setClassMetadata(ClassMetadata $classMetadata): self
    {
        $this->classMetadata = $classMetadata;

        return $this;
    }

    public function getClassName(): string
    {
        return $this->classMetadata->name;
    }

    public function getClassTableName(): string
    {
        return $this->classMetadata->table['name'];
    }

    public function createQueryBuilder(string $alias, string $indexBy = null): QueryBuilder
    {
        return $this
            ->createQueryBuilderWithoutSelect($alias, $indexBy)
            ->select($alias);
    }

    public function createQueryBuilderWithoutSelect(string $alias, string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->from($this->getClassName(), $alias, $indexBy);
    }

    /** @return object */
    public function get(int $id)
    {
        $entity = $this->find($id);
        $this->assertIsEntity($entity, ['id' => $id]);

        return $entity;
    }

    /** @return object */
    public function getOneBy(array $criteria, array $orderBy = null)
    {
        $entity = $this->findOneBy($criteria, $orderBy);
        $this->assertIsEntity($entity, $criteria);

        return $entity;
    }

    public function countAll(): int
    {
        return $this->countBy([]);
    }

    public function countBy(array $params): int
    {
        $queryBuilder = $this
            ->createQueryBuilderWithoutSelect('entityToCount', null)
            ->select('COUNT(DISTINCT entityToCount)');

        foreach ($params as $name => $value) {
            $sql = (is_array($value)) ? ' IN (:' . $name . 'Value)' : ' = :' . $name . 'Value';
            $queryBuilder
                ->andWhere('entityToCount.' . $name . $sql)
                ->setParameter($name . 'Value', $value);
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $id
     * @return object
     */
    public function find($id)
    {
        return $this
            ->getEntityManager()
            ->find($this->getClassName(), $id);
    }

    public function findAll(): array
    {
        return $this->findBy([]);
    }

    /**
     * @param int|null $limit
     * @param int|null $offset
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this
            ->getEntityManager()
            ->getUnitOfWork()
            ->getEntityPersister($this->getClassName())
            ->loadAll($criteria, $orderBy, $limit, $offset);
    }

    public function findReadOnlyBy(
        array $criteria,
        array $fields = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ): array {
        if ($fields === null) {
            $queryBuilder = $this->createQueryBuilder('entity');
        } else {
            $singleIdentifier = $this
                ->getEntityManager()
                ->getClassMetadata($this->getClassName())
                ->getSingleIdentifierFieldName();
            if (empty($singleIdentifier) === false && in_array($singleIdentifier, $fields) === false) {
                array_unshift($fields, $singleIdentifier);
            }
            $queryBuilder = $this
                ->createQueryBuilderWithoutSelect('entity')
                ->select('PARTIAL entity.{' . implode(', ', $fields) . '}');
        }

        foreach ($criteria as $criterion => $value) {
            $queryBuilder->andWhere('entity.' . $criterion . ' = ' . $value);
        }

        foreach ($orderBy ?? [] as $sort => $order) {
            $queryBuilder->addOrderBy($sort, $order);
        }

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult(ReadOnlyHydrator::HYDRATOR_NAME);
    }

    /** @return object */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this
            ->getEntityManager()
            ->getUnitOfWork()
            ->getEntityPersister($this->getClassName())
            ->load($criteria, null, null, [], null, 1, $orderBy);
    }

    /** @return object|Proxy */
    public function getPartialReference(int $id)
    {
        return $this->getEntityManager()->getPartialReference($this->getClassName(), $id);
    }

    protected function assertIsEntity($entity, array $criteria): self
    {
        if (is_object($entity) === false) {
            $message = 'Entity of type "' . $this->getClassMetadata()->getName() . '"';
            $criteriaForMessage = [];
            foreach ($criteria as $name => $value) {
                if (is_array($value)) {
                    $criteriaForMessage[] = $name . ' = ' . var_export($value, true);
                } else {
                    $criteriaForMessage[] = $name . ' = ' . $value;
                }
            }
            $message .= ' was not found with ' . (count($criteriaForMessage) > 1 ? ' criteria' : 'criterion');
            $message .= ' : ' . implode(', ', $criteriaForMessage);
            throw new EntityNotFoundException($message);
        }

        return $this;
    }

    protected function createQueryFromRawSql(string $alias, string $sql, callable $rsmbCallable = null): NativeQuery
    {
        $rsmb = new ResultSetMappingBuilder($this->getEntityManager());
        $rsmb->addRootEntityFromClassMetadata($this->getClassName(), $alias);
        if (is_callable($rsmbCallable)) {
            call_user_func($rsmbCallable, $rsmb);
        }

        return $this->getEntityManager()->createNativeQuery($sql, $rsmb);
    }

    protected function getClassMetadata(): ClassMetadata
    {
        return $this->classMetadata;
    }
}
