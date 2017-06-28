[![version](https://img.shields.io/badge/version-master-red.svg)](https://github.com/huttopia/doctrine)
[![symfony](https://img.shields.io/badge/php-^7.1.3-blue.svg)](http://www.php.net)
[![symfony](https://img.shields.io/badge/doctrine/orm-^2.5-blue.svg)](http://www.doctrine-project.org)
![Lines](https://img.shields.io/badge/code%20lines-227-green.svg)
![Total Downloads](https://poser.pugx.org/huttopia/doctrine/downloads)

### huttopia/doctrine

Doctrine is a really good ORM, with nice features, we love it !

But, to be honest, no major version since 2 april 2015, several bugs are not fixed
and it takes too much time to create patch version when you create a PR to fix something.

So, we decided to create huttopia/doctrine, to fix what we need, without waiting for a release.
We can add features too.

We decide to not fork Doctrine, because we want to follow Doctrine releases. Forking it now is nice and amazing, but in 2 years...

When we need to override a class, we do it with [steevanb/composer-overload-class](https://github.com/steevanb/composer-overload-class).
That's a good way when you need it, without renaming namespace everywhere (we can't, that's not a fork ;)). 

### Installation

Add it to your composer.json :

```bash
composer require huttopia/doctrine dev-master
```

### Doctrine bugs

Bugs who are fixed or not fixed by Doctrine, for some reasons :
- [steevanb/doctrine-events](https://github.com/steevanb/doctrine-events) Fix a Doctrine UnitOfwork bug with extraUpdates, who are not removed when you add and remove your entity before calling flush()
- [#6042 (not fixed)](https://github.com/doctrine/doctrine2/issues/6042) getId() lazy load entity if getId() is in trait : not fixed, just to remember why we don't use trait for getId()
- [#6110 (fixed)](https://github.com/doctrine/doctrine2/pull/6110) Clear $this->collection even when empty, to reset keys
- [#6509 (not fixed)](https://github.com/doctrine/doctrine2/issues/6509) PersistentCollection::clear() and removeElement() with orphanRemoval will remove tour entity, although you don't want

### Remove useless discriminator in SQL for single table inheritance

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

### Enable steevanb/doctrine-events

It will replace EntityManager, to add some events : onCreateEntityOverrideLocalValues, onCreateEntityDefineFieldValues, onNewEntityInstance etc.

See [Installation](https://github.com/steevanb/doctrine-events/blob/master/README.md#installation) to install it.

### Enable steevanb/doctrine-entity-merger

When you use PARTIAL in DQL, you retrieve only fields you need, instead of all Entity fields.

But, if you execute 2 PARTIAL on same entity, but not same fields, your final entity will not have second PARTIAL data, only first one is hydrated.

See [Installation](https://github.com/steevanb/doctrine-entity-merger#installation) to install it.

### Enable steevanb/doctrine-read-only-hydrator

See [Benchmark](https://github.com/steevanb/doctrine-read-only-hydrator#benchmark), you will understand why we use ReadOnlyHydrator ;)

See [Installation](https://github.com/steevanb/doctrine-read-only-hydrator#installation) to install it.

### Enable steevanb/doctrine-stats

[steevanb/doctrine-stats](https://github.com/steevanb/doctrine-stats) add lot of statistics about queries, hydration time, lazy loaded entities, etc.

See [Installation](https://github.com/steevanb/doctrine-stats#installation) to install it.
