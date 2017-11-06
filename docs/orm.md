# The Joomla ORM

The Joomla! **ORM** package provides a storage access abstraction using repositories for your application.

Entities can be plain objects. No interface needs to be implemented.

## RepositoryFactory

The programmatic entry-point to the ORM is the `RepositoryFactory`. It manages the creation of all necessary ORM internal dependancies. The factory is usually provided by the `StorageServiceProvider` with the key '`Repository`' in a PSR compatible dependency injection container.

The `RepositoryFactory` exposes one public method, `forEntity()`.

```php
/** @var \Psr\Container\ContainerInterface $container */
$repository = $container->get('Repository')->forEntity($entityClassOrAlias);
```

You can inject a DataMapper, if you want to override the configuration settings.

```php
/** @var \Psr\Container\ContainerInterface $container */
$repository = $container->get('Repository')->forEntity($entityClassOrAlias, $dataMapper);
```

## Repository

A `Repository` is created by the `RepositoryFactory`. It should not be instantiated directly outside the ORM, because that could do harm to the referential integrity.

```php
/** @var \Psr\Container\ContainerInterface $container */
$repository = $container->get('Repository')->forEntity(Article::class);
```

The interface for a Repository is:

```php
interface RepositoryInterface
{
	public function getById($id);
	public function findOne();
	public function findAll();
	public function add($entity);
	public function remove($entity);
	public function commit();
	public function getEntityClass()
	public function restrictTo($lValue, $op, $rValue);
}
```

`commit()` is a proxy to the `UnitOfWork`.

Since the repository is used as a collection in relations, the `restrictTo()` method is used to preset conditions, so access is restricted to related entities.

## DataMapper

The `DataMapperInterface` is similar to the `RepositoryInterface`:

```php
interface DataMapperInterface
{
	public function getById($id);
	public function findOne();
	public function findAll();
	public function insert($entity);
	public function update($entity);
	public function delete($entity);
}
```

Current implementations for this interface are `CsvDataMapper` and `DoctrineDataMapper`.

### CsvDataMapper

The `CsvDataMapper`'s constructor takes four arguments:

  - the CsvDataGateway,
  - the class of the entity,
  - the basename of the data file, and
  - the global entity registy.

```php
$dataMapper = new CsvDataMapper(
    $gateway,
    Article::class,
    'articles',
    $entityRegistry
);
```

### DoctrineDataMapper

The `DoctrineDataMapper`'s constructor takes four arguments:

  - the database connection,
  - the class of the entity,
  - the name of the table, and
  - the global entity registy.

```php
$dataMapper = new DoctrineDataMapper(
    $connection,
    Article::class,
    'articles',
    $entityRegistry
);
```

## EntityBuilder, EntityRegistry

The entities and their relations are managed by a couple of ORM internal classes. Userland code should never have to deal with them. 

### Configuration

The setup information for the ORM is currently stored in an `entities.ini` file.

## Testing

The tests have been re-organised, so they can be re-used for any storage type.
They are located in

  - `tests/unit/ORM/StorageTestCases.php`,
    which is extended by `CsvStorageTest` and `DoctrineStorageTest`,
  - `tests/unit/ORM/RelationTestCases.php`,
    extended by `CsvRelationTest` and `DoctrineRelationTest`, and
  - `tests/unit/ORM/DataMapperTestCases.php`,
    extended by `CsvDataMapperTest` and `DoctrineDataMapperTest`.

This way, all tests are run for all data mappers, ensuring identical behaviour.

### Test Data

The test data is provided in `tests/unit/ORM/data/original`. An accessible copy for the tests for both CSV and SQLite are created automatically on each run of PHPUnit.

### Run the tests

To run the tests, just call PHPUnit:

```bash
$ ./vendor/bin/phpunit
```

You'll see this output (as of Nov 2017):

```
Copying sqlite.test.db
Copying articles.csv
Copying details.csv
Copying extras.csv
Copying maps.csv
Copying masters.csv
Copying tags.csv
Copying users.csv
PHPUnit 6.3.0 by Sebastian Bergmann and contributors.

...............................................................  63 / 189 ( 33%)
............................................................... 126 / 189 ( 66%)
............................................................... 189 / 189 (100%)


Time: 13.32 seconds, Memory: 14.00MB

OK (189 tests, 338 assertions)
```
