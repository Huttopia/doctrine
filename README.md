[![version](https://img.shields.io/badge/version-master-red.svg)](https://github.com/huttopia/doctrine)
[![symfony](https://img.shields.io/badge/php-^7.1.3-blue.svg)](http://www.php.net)
[![symfony](https://img.shields.io/badge/doctrine/orm-^2.5-blue.svg)](http://www.doctrine-project.org)
![Lines](https://img.shields.io/badge/code%20lines-unknow-green.svg)
![Total Downloads](https://poser.pugx.org/huttopia/doctrine/downloads)

# doctrine

New features and fix for [Doctrine](https://github.com/doctrine/doctrine2)

# Installation

Add it to your composer.json :

```bash
composer require huttopia/doctrine dev-master
```

# Remove useless disciminator in SQL for single table inheritance

With SINGLE_TABLE_INHERITANCE entities, Doctrine add discriminator columns into all SQL queries.

But if you want to query all entities, Doctrine add useless WHERE clause with discriminator : not really good for performances ;)

Huttopia\Doctrine\SqlWalker\IgnoreDiscriminator override Doctrine SqlWalker, to add WHERE clause only when needed.

Add it for a single query :
```php
use Huttopia\Doctrine\SqlWalker\IgnoreDiscriminator

$queryBuilder
    ->getQuery()
    ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, IgnoreDiscriminator::class);
```

Add it for all queries :
```php
$manager
    ->getConfiguration()
    ->setDefaultQueryHint(
        Query::HINT_CUSTOM_OUTPUT_WALKER,
        IgnoreDiscriminator::class
    );
```
