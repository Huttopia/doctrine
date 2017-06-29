<?php

declare(strict_types=1);

namespace Huttopia\Doctrine\Tests;

use PHPUnit\Framework\TestCase;

/** @group PersistentCollection */
class PersistentCollectionTest extends TestCase
{
    /** Assert Doctrine PersistentCollection is not modified, cause we override it */
    public function testFileContent()
    {
        $this->assertEquals(
            file_get_contents(__DIR__ . '/PersistentCollection.phps'),
            file_get_contents(__DIR__ . '/../../../doctrine/orm/lib/Doctrine/ORM/PersistentCollection.php')
        );
    }
}
