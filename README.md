[![version](https://img.shields.io/badge/version-1.1.0-green.svg)](https://github.com/huttopia/doctrine/releases/tag/1.1.0)
[![symfony](https://img.shields.io/badge/php-^7.1.3-blue.svg)](http://www.php.net)
[![symfony](https://img.shields.io/badge/doctrine/orm-^2.5-blue.svg)](http://www.doctrine-project.org)
[![symfony](https://img.shields.io/badge/symfony/symfony-^3.0-blue.svg)](https://symfony.com/)
![Lines](https://img.shields.io/badge/code%20lines-1422-green.svg)
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

[Changelog](changelog.md)

### Installation

Add it to your composer.json :

```bash
composer require huttopia/doctrine ^1.1
```

Register HuttopiaDoctrineBundle :
```php
# app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles(): array
    {
        $bundles = [
            new Huttopia\Doctrine\Bridge\Symfony3\HuttopiaDoctrineBundle()
        ];

        return $bundles;
    }
}
```

Change Doctrine configuration :
```yml
# app/config/config.yml

doctrine:
    orm:
        repository_factory: huttopia.doctrine.repository_factory
        default_repository_class: Huttopia\Doctrine\Orm\EntityRepository
```

Configuration :
```yml
# app/config/config.yml

huttopia_doctrine:
    repository_factory_service: huttopia.doctrine.repository_factory # this is default value
```

### Doctrine bugs

Bugs who are fixed or not fixed by Doctrine, for some reasons :
- [fixed by steevanb/doctrine-events](https://github.com/steevanb/doctrine-events) Fix a Doctrine UnitOfwork bug with extraUpdates, who are not removed when you add and remove your entity before calling flush()
- [#6042 (not fixed)](https://github.com/doctrine/doctrine2/issues/6042) getId() lazy load entity if getId() is in trait : not fixed, just to remember why we don't use trait for getId()
- [#6110 (fixed)](https://github.com/doctrine/doctrine2/pull/6110) Clear $this->collection even when empty, to reset keys
- [#6509 (fixed here)](https://github.com/doctrine/doctrine2/issues/6509) PersistentCollection::clear() and removeElement() with orphanRemoval will remove tour entity, although you don't want

### Repositories as service

Yes, you need it too ;) Repositories as service is one of the biggest improvement.

Now, we can define your repository as service, with huttopia.repository tag :
```yml
services:
    bar_repository:
        class: Foo\Repository\BarRepository
        arguments: ['@service', '%parameter%']
        tags:
            - { name: huttopia.repository, entity: Foo\Bar }

```

You need to change _extends Doctrine\ORM\EntityRepository_ by _extends Huttopia\Doctrine\Orm\EntityRepository_ in your repositories.

Take care, our repository remove magic methods (findOneById() for example).

But it add a lot of methods :
- getClassName(): string
- getClassTableName(): string
- createQueryBuilderWithoutSelect(string $alias, string $indexBy = null): QueryBuilder
- get(int $id): Entity
- getOneBy(array $criteria, array $orderBy = null): Entity
- countAll(): int
- countBy(array $params): int
- findReadOnlyBy(array $criteria, array $fields = null, array $orderBy = null, $limit = null, $offset = null): array
- getPartialReference(int $id)

Difference between find() and get(), and findOneBy() and getOneBy() : when entity is not found, find() will return null, as get() will throw an exception.

When you use PARTIAL, you can call createQueryBuilderWithoutSelect() instead of createQueryBuilder(), who will not select all root entity fields.

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
# app/AppKernel.php

class AppKernel
{
    public function boot(): void
    {
        parent::boot();

        foreach ($this->getContainer()->get('doctrine')->getManagers() as $manager) {
            if ($manager instanceof EntityManagerInterface) {
                $manager->getConfiguration()->setDefaultQueryHint(
                    Query::HINT_CUSTOM_OUTPUT_WALKER,
                    IgnoreDiscriminator::class
                );
            }
        }
    }
}
```

### [#6509](https://github.com/doctrine/doctrine2/issues/6509) Fix PersistentCollection orphanRemoval management

When you call _remove()_, _removeElement()_ or _clear()_ on _PersistentCollection_, and your manyToOne configuration define _orphanRemoval_ as true,
PersistentCollection will add your deleted entity in UnitOfWork::$orphanRemovals.

flush() will read UnitOfWork::$orphanRemovals, and delete all entities, although they are deleted then added.

So, if you remove an entity, then add it again, then flush(), finally, your entity will be deleted.

To fix it, we override _PersistentCollection_, and remove all _orphanRemoval_ managements in it.
Take care with it, you need to manually remove link between entities now (as we should do).

For example, User -> oneToMany -> Comment : you need to call _$comment->setUser(null)_ in _User::removeComment(Comment $comment)_.

See [ComposerOverloadClass installation](https://github.com/steevanb/composer-overload-class).

Override _PersistentCollection_ to fix it :
```yml
{
    "extra": {
        "composer-overload-class": {
            "Doctrine\\ORM\\PersistentCollection": {
                "original-file": "vendor/doctrine/orm/lib/Doctrine/ORM/PersistentCollection.php",
                "overload-file": "vendor/huttopia/doctrine/ComposerOverloadClass/Orm/PersistentCollection.php",
                "replace": true
            }
        }
    }
}
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
