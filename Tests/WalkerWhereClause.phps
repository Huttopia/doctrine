    public function walkWhereClause($whereClause)
    {
        $condSql  = null !== $whereClause ? $this->walkConditionalExpression($whereClause->conditionalExpression) : '';
        $discrSql = $this->_generateDiscriminatorColumnConditionSql($this->rootAliases);

        if ($this->em->hasFilters()) {
            $filterClauses = array();
            foreach ($this->rootAliases as $dqlAlias) {
                $class = $this->queryComponents[$dqlAlias]['metadata'];
                $tableAlias = $this->getSQLTableAlias($class->table['name'], $dqlAlias);

                if ($filterExpr = $this->generateFilterConditionSQL($class, $tableAlias)) {
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
            return ' WHERE ' . (( ! $discrSql) ? $condSql : '(' . $condSql . ') AND ' . $discrSql);
        }

        if ($discrSql) {
            return ' WHERE ' . $discrSql;
        }

        return '';
    }
