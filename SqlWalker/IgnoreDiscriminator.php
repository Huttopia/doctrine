<?php

declare(strict_types=1);

namespace Huttopia\Doctrine\SqlWalker;

use Doctrine\ORM\Query\SqlWalker;

class IgnoreDiscriminator extends SqlWalker
{
    /**
     * Remove useless discriminator in SQL for single table inheritance
     * Some lines are copied from SqlWalker::walkWhereClause
     */
    public function walkWhereClause($whereClause): string
    {
        $generateDiscriminatorColumnConditionSql = new \ReflectionMethod(
            'Doctrine\ORM\Query\SqlWalker',
            '_generateDiscriminatorColumnConditionSql'
        );
        $generateDiscriminatorColumnConditionSql->setAccessible(true);

        $generateFilterConditionSQL = new \ReflectionMethod(
            'Doctrine\ORM\Query\SqlWalker',
            'generateFilterConditionSQL'
        );
        $generateFilterConditionSQL->setAccessible(true);

        $rootAliasesValue = $this->getPrivatePropertyValue(SqlWalker::class, 'rootAliases');
        $queryComponentsValue = $this->getPrivatePropertyValue(SqlWalker::class, 'queryComponents');

        $condSql = $whereClause !== null ? $this->walkConditionalExpression($whereClause->conditionalExpression) : '';

        if (
            array_key_exists('metadata', $queryComponentsValue[array_keys($queryComponentsValue)[0]])
            && $queryComponentsValue[array_keys($queryComponentsValue)[0]]['metadata']->discriminatorValue === null
        ) {
            $discrSql = '';
        } else {
            $discrSql = $generateDiscriminatorColumnConditionSql->invoke($this, $rootAliasesValue);
        }

        if ($this->getEntityManager()->hasFilters()) {
            $filterClauses = [];
            foreach ($rootAliasesValue as $dqlAlias) {
                $class = $queryComponentsValue[$dqlAlias]['metadata'];
                $tableAlias = $this->getSQLTableAlias($class->table['name'], $dqlAlias);

                if ($filterExpr = $generateFilterConditionSQL->invoke($this, $class, $tableAlias)) {
                    $filterClauses[] = $filterExpr;
                }
            }

            if (count($filterClauses)) {
                if ($condSql) {
                    $condSql = '(' . $condSql . ') AND ';
                }

                $condSql .= implode(' AND ', $filterClauses);
            }
        }

        if ($condSql) {
            $return = ' WHERE ' . (( ! $discrSql) ? $condSql : '(' . $condSql . ') AND ' . $discrSql);
        } elseif ($discrSql) {
            $return = ' WHERE ' . $discrSql;
        } else {
            $return = '';
        }

        return $return;
    }

    /** @return mixed */
    protected function getPrivatePropertyValue(string $class, string $property)
    {
        $class = new \ReflectionClass($class);
        $rootAliases = $class->getProperty($property);
        $rootAliases->setAccessible(true);
        $return = $rootAliases->getValue($this);
        $rootAliases->setAccessible(false);

        return $return;
    }
}
