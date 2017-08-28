### [1.2.3](../../compare/1.2.2...1.2.3) (2017-08-28)

[#9](https://github.com/Huttopia/doctrine/pull/9) Add entity single identifier to _EntityRepository::findReadOnlyBy()_ if it's not asked in _$fields_

### [1.2.2](../../compare/1.2.1...1.2.2) (2017-08-01)

- Fix doctrine to 2.5.6 : ^2.5.6 has wrong tag (some dependencies are on dev-master, whithout version), some BC etc

### [1.2.1](../../compare/1.2.0...1.2.1) (2017-07-26)

- Fix doctrine to 2.5.* : 2.6 has wrong tag (some dependencies are on dev-master, whithout version), some BC etc

### [1.2.0](../../compare/1.1.0...1.2.0) (2017-06-29)

- Update _steevanb/composer-overload-class_ dependency to 1.3.0

### [1.1.0](../../compare/1.0.0...1.1.0) (2017-06-29)

- Fix [#6509](https://github.com/doctrine/doctrine2/issues/6509): PersistentCollection orphanRemoval management

### 1.0.0 (2017-06-29)

- Ignore useless discriminator for single table inheritance, with Huttopia\Doctrine\SqlWalker\IgnoreDiscriminator
- Repositories as service
- Bridge with Symfony3
- Add steevanb/doctrine-read-only-hydrator to dependencies
- Add steevanb/doctrine-entity-merger to dependencies
- Add steevanb/doctrine-events to dependencies
- Add steevanb/doctrine-stats to dependencies
