# The ORM Package [![Build Status](https://travis-ci.org/joomla-x/orm.png?branch=master)](https://travis-ci.org/joomla-x/orm)

The Joomla! **ORM** package provides a storage access abstraction using repositories for your application.

**This package is in a pre-alpha state; use it to get familiar with it and to improve it, but do not use it in production, unless you really know what you are doing.**

## Installation via Composer

Simply run the following from the command line in your project's root directory (where your `composer.json` file is):

```sh
composer require joomla-x/orm:dev-master
```
## Contributing

Please review [https://framework.joomla.org/contribute](https://framework.joomla.org/contribute) for information
on how to contribute to the Framework's development.

## ToDo

- [ ] Implement handling of entity role
    - [x] Don't create reverse relations for `lookup` tables
- [ ] Implement fieldset handling
- [ ] Relation Handling
    - [x] Implement belongsToMany handling on installation
    - [ ] Implement handling of belongsToMany relations in UnitOfWork::checkForChangedRelations()
    - [ ] Implement handling of hasMany relations in UnitOfWork::checkForChangedRelations()
    - [ ] Implement handling of hasManyThrough relations in UnitOfWork::checkForChangedRelations()
- [ ] Move Entity DTD to a Joomla repository when it is stable enough
- [x] Replace DTD with XMLSchema, so `<xs:alternative test="@type=string">` can be used to specify attributes
      that are specific to certain field types. See [this StackOverflow answer](https://stackoverflow.com/questions/27878402/how-to-make-type-depend-on-attribute-value-using-conditional-type-assignment#answer-27880329) for more information.
- [ ] Apply validation according to definition in `EntityBuilder::castToEntity()`
- [ ] Use entity name instead of table name in `EntityBuilder::resolveHasManyThrough()`
- [ ] Add `__call()` method to Repository to proxy any `get*()` methods from the DataMappers
- [ ] Implement handler selection according to entity definition
