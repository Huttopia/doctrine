<?php

declare(strict_types=1);

namespace Huttopia\Doctrine\Tests;

use Doctrine\ORM\Query\SqlWalker;
use PHPUnit\Framework\TestCase;

/** @group DoctrineSQlWalker */
class DoctrineSQlWalkerTest extends TestCase
{
    /** Check SqlWalker::walkWhereClause */
    public function testWalkWhereClause()
    {
        $class = new \ReflectionClass(SqlWalker::class);
        $method = $class->getMethod('walkWhereClause');

        $sourceArray = explode("\n", file_get_contents($class->getFileName()));
        $compareSource = file_get_contents(__DIR__ . '/WalkerWhereClause.phps');

        $startLine = $method->getStartLine() - 1;
        $length = $method->getEndLine() - $startLine + 1;
        $sourceArray = array_slice($sourceArray, $startLine, $length);

        $this->assertTrue(trim(implode("\n", $sourceArray)) === trim($compareSource));
    }
}
