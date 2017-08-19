<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Entity;

use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\NullDispatcher;
use Joomla\ORM\Definition\Locator\LocatorInterface;
use Joomla\ORM\Definition\Locator\Strategy\StrategyInterface;
use Joomla\ORM\Definition\Parser\BelongsTo;
use Joomla\ORM\Definition\Parser\BelongsToMany;
use Joomla\ORM\Definition\Parser\Element;
use Joomla\ORM\Definition\Parser\Entity as EntityStructure;
use Joomla\ORM\Definition\Parser\Field;
use Joomla\ORM\Definition\Parser\HasMany;
use Joomla\ORM\Definition\Parser\HasManyThrough;
use Joomla\ORM\Definition\Parser\HasOne;
use Joomla\ORM\Definition\Parser\XmlParser;
use Joomla\ORM\Event\CreateDefinitionEvent;
use Joomla\ORM\Event\DefinitionCreatedEvent;
use Joomla\ORM\Exception\EntityNotDefinedException;
use Joomla\ORM\Exception\EntityNotFoundException;
use Joomla\ORM\Operator;
use Joomla\ORM\Repository\MappingRepository;
use Joomla\ORM\Repository\RepositoryInterface;
use Joomla\ORM\Service\RepositoryFactory;

/**
 * Class EntityBuilder
 *
 * @package  Joomla/ORM
 *
 * @since    __DEPLOY_VERSION__
 */
class EntityBuilder
{
    private $special = [
        'id'               => 'id',
        'name'             => 'title',
        'title'            => 'title',
        'state'            => 'state',
        'ordering'         => 'ordering',
        'checked_out'      => 'checked_out_by',
        'checked_out_time' => 'checked_out_time',
        'created_by'       => 'created_by',
        'created_time'     => 'created_time',
        'modified_by'      => 'modified_by',
        'modified_time'    => 'modified_time',
        'hits'             => 'hits',
        'clicks'           => 'hits',
    ];

    private $fieldAliases = [];

    /** @var  LocatorInterface  The XML definition file locator */
    private $locator;

    /** @var  string  Prefix for language file keys */
    private $prefix;

    /** @var  EntityInterface[]  Entities */
    private $entities;

    /** @var  RepositoryFactory */
    private $repositoryFactory;

    /** @var  EntityReflector  Reflector to manipulate the entity */
    private $reflector;

    /** @var array Entity aliases */
    private $alias = [];

    use DispatcherAwareTrait;

    /**
     * Constructor
     *
     * @param   LocatorInterface  $locator           The XML description file locator
     * @param   RepositoryFactory $repositoryFactory The repository factory
     */
    public function __construct(LocatorInterface $locator, RepositoryFactory $repositoryFactory)
    {
        $this->locator           = $locator;
        $this->repositoryFactory = $repositoryFactory;

        $this->setDispatcher(new NullDispatcher);
    }

    public function addLocatorStrategy(StrategyInterface $strategy)
    {
        $this->locator->add($strategy);
    }

    /**
     * Parser callback for onBeforeEntity event
     *
     * @internal
     *
     * @param   array $attributes The element attributes
     *
     * @return  array
     */
    public function prepareEntity($attributes)
    {
        $this->fieldAliases  = [];
        $attributes['class'] = $attributes['name'];
        $attributes['name']  = preg_replace('~^.*?(\w+)$~', '\1', $attributes['class']);
        $this->prefix        = 'COM_' . strtoupper($attributes['name']) . '_FIELD_';

        return $attributes;
    }

    /**
     * Parser callback for onAfterEntity event
     *
     * @internal
     *
     * @param   EntityStructure $element The data structure
     *
     * @return  void
     */
    public function handleEntity($element)
    {
        $element->fieldAliases = $this->fieldAliases;
    }

    /**
     * Parser callback for onAfterField event
     *
     * @internal
     *
     * @param   Field $field The data structure
     *
     * @return  void
     */
    public function handleField(Field $field)
    {
        $prefix = $this->prefix . strtoupper($field->name);

        if (!isset($field->label)) {
            $field->label = $prefix . '_LABEL';
        }

        if (preg_match('~^([A-Z_]+)$~', $field->label, $match)) {
            $prefix = $match[1];
        }

        if (preg_match('~^([A-Z_]+?)_LABEL$~', $field->label, $match)) {
            $prefix = $match[1];
        }

        if (!isset($field->description)) {
            $field->description = $prefix . '_DESC';
        }

        if (preg_match('~^([A-Z_]+?)_DESC$~', $field->description, $match)) {
            $prefix = $match[1];
        }

        if (!isset($field->hint)) {
            $field->hint = $prefix . '_HINT';
        }

        if (!isset($field->role)) {
            if (isset($this->special[$field->name])) {
                $field->role = $this->special[$field->name];
            }
        }

        if (isset($field->role)) {
            $this->fieldAliases[$field->role] = $field->name;
        }

        $this->reflector->addField($field);
    }

    /**
     * Parser callback for onBeforeOption event
     *
     * @internal
     *
     * @param   array $attributes The element attributes
     *
     * @return  array
     */
    public function prepareOption($attributes)
    {
        #echo "<pre>";
        #echo "Option attributes:\n";
        #print_r($attributes);
        #echo "</pre>";

        return $attributes;
    }

    /**
     * Parser callback for onAfterOption event
     *
     * @internal
     *
     * @param   EntityStructure $element The data structure
     *
     * @return  void
     */
    public function handleOption($element)
    {
        #echo "<pre>";
        #echo "Option element:\n";
        #print_r($element);
        #echo "</pre>";
    }

    /**
     * Parser callback for onBeforeFieldset event
     *
     * @internal
     *
     * @param   array $attributes The element attributes
     *
     * @return  array
     */
    public function prepareFieldset($attributes)
    {
        #echo "Fieldset attributes:\n";
        #print_r($attributes);

        return $attributes;
    }

    /**
     * Parser callback for onAfterFieldset event
     *
     * @internal
     *
     * @param   EntityStructure $element The data structure
     *
     * @return  void
     */
    public function handleFieldset($element)
    {
        #echo "Fieldset element:\n";
        #print_r($element);
    }

    /**
     * Cast array to entity
     *
     * @param   array  $matches     The records
     * @param   string $entityClass The entity name
     *
     * @return array
     */
    public function castToEntity($matches, $entityClass)
    {
        $entityRegistry = $this->repositoryFactory->getEntityRegistry();

        $result = [];

        $meta        = $this->getMeta($entityClass);
        $entityClass = $meta->class;
        $reflection  = new \ReflectionClass($entityClass);

        foreach ($matches as $match) {
            $entity = new $entityClass;

            foreach ($meta->fields as $key => $definition) {
                /** @var Element $definition */
                $varName = $definition->propertyName($key);
                $colName = $definition->columnName($key);

                $value = isset($definition->default) ? $definition->default : null;

                if (array_key_exists($colName, $match)) {
                    // @todo Apply validation according to definition
                    $value = $match[$colName];

                    if ($definition instanceof Field) {
                        switch ($definition->type) {
                            case 'int':
                            case 'integer':
                                $value = (integer)$value;
                                break;

                            case 'json':
                                $value = json_decode($value);
                                break;

                            default:
                                // Leave the value alone
                                break;
                        }
                    }
                }

                if ($reflection->hasProperty($varName)) {
                    $property = $reflection->getProperty($varName);
                    $property->setAccessible(true);
                    $property->setValue($entity, is_null($value) ? $entity->{$varName} : $value);
                } else {
                    /** @noinspection PhpVariableVariableInspection */
                    $entity->{$varName} = $value;
                }
            }

            $entityRegistry->stashEntity($entity);
            $this->resolveRelations($entity, $meta);
            $entityRegistry->registerEntity($entity);

            $result[] = $entity;

            unset($entity);
        }

        return $result;
    }

    /**
     * Get the meta data for the entity type
     *
     * @param   string $entityClass The entity name
     *
     * @return  EntityStructure
     */
    public function getMeta($entityClass)
    {
        return $this->entities[$this->resolveAlias($entityClass)]->getDefinition();
    }

    public function resolveAlias($alias)
    {
        while (isset($this->alias[$alias]) && $this->alias[$alias] != $alias) {
            $alias = $this->alias[$alias];
        }

        if (!isset($this->entities[$alias])) {
            $alias = $this->readDefinition($alias);
        }

        return $alias;
    }

    /**
     * @param $entityName
     *
     * @return string
     */
    private function readDefinition($entityName)
    {
        $this->getDispatcher()->dispatch('onCreateDefinition', new CreateDefinitionEvent($entityName, $this));

        $entity          = new Entity;
        $this->reflector = new EntityReflector($entity);
        $filename        = $this->locateDescription($entityName);
        $definition      = $this->parseDescription($filename, $entityName);
        $this->reflector->setDefinition($definition);
        $this->entities[$definition->class] = $entity;

        $this->alias[$definition->name] = $definition->class;

        $this->setIdAccessors($definition->class, $entity->key());

        $this->getDispatcher()->dispatch(
            'onDefinitionCreated',
            new DefinitionCreatedEvent($definition->class, $definition, $this)
        );

        return $definition->class;
    }

    /**
     * Locate the description file
     *
     * @param   string $entityClass The class of the entity
     *
     * @return  string  The definition file path
     */
    private function locateDescription($entityClass)
    {
        $entityName     = preg_replace('~^.*?(\w+)$~', '\1', $entityClass);
        $definitionFile = $entityName . '.xml';
        $filename       = $this->locator->findFile($definitionFile);

        if (!is_null($filename)) {
            return $filename;
        }

        throw new EntityNotDefinedException($entityClass);
    }

    /**
     * Parse the description file
     *
     * @param   string $filename    The definition file path
     * @param   string $entityClass The class of the entity
     *
     * @return  EntityStructure  The parsed description
     */
    private function parseDescription($filename, $entityClass)
    {
        $parser = new XmlParser();

        $parser->open($filename);

        $definition = $parser->parse([
            'onBeforeEntity'   => [$this, 'prepareEntity'],
            'onAfterEntity'    => [$this, 'handleEntity'],
            'onAfterField'     => [$this, 'handleField'],
            'onBeforeOption'   => [$this, 'prepareOption'],
            'onAfterOption'    => [$this, 'handleOption'],
            'onBeforeFieldset' => [$this, 'prepareFieldset'],
            'onAfterFieldset'  => [$this, 'handleFieldset'],
        ], $this->locator);

        return $definition;
    }

    /**
     * @param $entityClass
     * @param $key
     */
    private function setIdAccessors($entityClass, $key)
    {
        $idAccessorRegistry = $this->repositoryFactory->getIdAccessorRegistry();

        if (is_array($key)) {
            $getter = function ($entity) use ($key) {
                $id = [];
                foreach ($key as $property) {
                    $id[$property] = $entity->{$property};
                }

                return $id;
            };
            $setter = function ($entity, $id) use ($key) {
                foreach ($key as $property) {
                    $entity->{$property} = $id[$property];
                }
            };
            $idAccessorRegistry->registerIdAccessors($entityClass, $getter, $setter);

            return;
        }

        if (!empty($key)) {
            $idAccessorRegistry->registerReflectionIdAccessors($entityClass, $key);
        }
    }

    /**
     * @param $entity
     * @param $meta
     *
     * @throws \Exception
     */
    private function resolveRelations($entity, $meta)
    {
        $reflection = new \ReflectionClass($entity);
        $entityId   = $this->repositoryFactory->getIdAccessorRegistry()->getEntityId($entity);

        $this->resolveBelongsTo($meta->relations['belongsTo'], $entity, $reflection);
        $this->resolveBelongsToMany($meta->relations['belongsToMany'], $entity, $reflection);
        $this->resolveHasOne($meta->relations['hasOne'], $entity, $entityId);
        $this->resolveHasMany($meta->relations['hasMany'], $entity, $entityId);
        $this->resolveHasManyThrough($meta->relations['hasManyThrough'], $entity, $entityId);
    }

    /**
     * @param BelongsTo[]      $relations
     * @param object           $entity
     * @param \ReflectionClass $reflection
     */
    private function resolveBelongsTo($relations, $entity, $reflection)
    {
        foreach ($relations as $field => $relation) {
            /** @var BelongsTo $relation */
            $colIdName  = $relation->colIdName();
            $varIdName  = $relation->varIdName();
            $varObjName = $relation->varObjectName();

            #echo "\n BelongsTo \n field = $field \n varIdName = $varIdName \n varObjName = $varObjName \n";

            $entityClass = $this->resolveAlias($relation->entity);
            $repository  = $this->getRepository($entityClass);

            if ($reflection->hasProperty($varIdName)) {
                $property = $reflection->getProperty($varIdName);
                $property->setAccessible(true);
                $objectId = $property->getValue($entity);
            } else {
                /** @noinspection PhpVariableVariableInspection */
                $objectId = $entity->{$varIdName};
            }

            try {
                $object = !empty($objectId) ? $repository->getById($objectId) : null;
            } catch (EntityNotFoundException $e) {
                $object = null;
            }
            #echo "this.$colIdName --> $entityClass.id\n";

            $entity->{$varObjName} = $object;
        }
    }

    /**
     * Gets a repository for an entity class
     *
     * @param   string $entityClass The entity class
     *
     * @return  RepositoryInterface
     */
    public function getRepository($entityClass)
    {
        return $this->repositoryFactory->forEntity($this->resolveAlias($entityClass));
    }

    /**
     * @param BelongsToMany[]  $relations
     * @param object           $entity
     * @param \ReflectionClass $reflection
     */
    private function resolveBelongsToMany($relations, $entity, $reflection)
    {
        foreach ($relations as $field => $relation) {
            /** @var BelongsToMany $relation */
            $colIdName  = $relation->colIdName();
            $varIdName  = $relation->varIdName();
            $varObjName = $relation->varObjectName();

            $entityClass = $this->resolveAlias($relation->entity);
            $repository  = $this->getRepository($entityClass);

            if ($reflection->hasProperty($varIdName)) {
                $property = $reflection->getProperty($varIdName);
                $property->setAccessible(true);
                $objectIds = $property->getValue($entity);
            } else {
                /** @noinspection PhpVariableVariableInspection */
                $objectIds = $entity->{$varIdName};
            }

            if (!is_array($objectIds)) {
                $objectIds = explode(',', $objectIds);
            }

            try {
                $objects = !empty($objectIds) ? $repository->findAll()
                                                           ->with('id', Operator::IN, $objectIds)
                                                           ->getItems() : null;
            } catch (EntityNotFoundException $e) {
                $objects = [];
            }

            $entity->{$varObjName} = $objects;
        }
    }

    /**
     * @param HasOne[]   $relations
     * @param object     $entity
     * @param int|string $entityId
     */
    private function resolveHasOne($relations, $entity, $entityId)
    {
        $entityRegistry = $this->repositoryFactory->getEntityRegistry();

        foreach ($relations as $field => $relation) {
            /** @var HasOne $relation */
            $varObjName = $relation->varObjectName();
            $colRefName = $relation->colReferenceName();

            #echo "\n HasOne \n field = $field \n varObjName = $varObjName \n colRefName = $colRefName \n";

            $entityClass = $this->resolveAlias($relation->entity);

            $object = $entityRegistry->getEntity($entityClass, $entityId);

            if (empty($object)) {
                $repository = $this->getRepository($entityClass);

                try {
                    $object = $repository
                        ->findOne()
                        ->with($colRefName, Operator::EQUAL, $entityId)
                        ->getItem()
                    ;
                } catch (EntityNotFoundException $e) {
                    $object = null;
                }
            }

            #echo "this.id <-- $entityClass.$colRefName\n";
            $entity->{$varObjName} = $object;
        }
    }

    /**
     * @param HasMany[]  $relations
     * @param object     $entity
     * @param int|string $entityId
     */
    private function resolveHasMany($relations, $entity, $entityId)
    {
        foreach ($relations as $field => $relation) {
            /** @var HasMany $relation */
            $varObjName = $relation->varObjectName();
            $colRefName = $relation->colReferenceName();

            #echo "\n HasMany \n field = $field \n varObjName = $varObjName \n colRefName = $colRefName \n";

            $entityClass = $this->resolveAlias($relation->entity);
            $repository  = $this->getRepository($entityClass);
            $repository->restrictTo($colRefName, Operator::EQUAL, $entityId);

            #echo "this.id <-- $entityClass.$colRefName \n";
            $entity->{$varObjName} = $repository;
        }
    }

    /**
     * @param HasManyThrough[] $relations
     * @param object           $entity
     * @param int|string       $entityId
     */
    private function resolveHasManyThrough($relations, $entity, $entityId)
    {
        foreach ($relations as $field => $relation) {
            /** @var HasManyThrough $relation */
            $varObjName = $relation->varObjectName();
            $colRefName = $relation->colReferenceName();

            #echo "\n HasManyThrough \n field = $field \n varObjName = $varObjName \n colRefName = $colRefName \n";

            // @todo Use entity name instead of uppercase table
            $mapClass = $this->resolveAlias(ucfirst($relation->joinTable));
            $mapRepo  = $this->getRepository($mapClass);
            $mapRepo->restrictTo($colRefName, Operator::EQUAL, $entityId);

            $entityClass = $this->resolveAlias($relation->entity);
            $repository  = $this->getRepository($entityClass);

            #echo "this.id <-- $mapClass.$colRefName | $mapClass.$relation->joinRef --> $entityClass.id \n";
            $entity->{$varObjName} = new MappingRepository(
                $repository,
                $mapRepo,
                $relation,
                $this->repositoryFactory->getUnitOfWork()
            );
        }
    }

    /**
     * Reduce relations
     *
     * @param object $entity
     *
     * @return array
     * @throws \Exception
     */
    public function reduce($entity)
    {
        $entityClass        = get_class($entity);
        $meta               = $this->getMeta($entityClass);

        $reflection = new \ReflectionClass($entity);
        $properties = [];

        foreach ($meta->fields as $name => $field) {
            $varName = $field->propertyName($field->name);
            $colName = $field->columnName($field->name);

            $value = null;

            if ($reflection->hasProperty($varName)) {
                $property = $reflection->getProperty($varName);
                $property->setAccessible(true);
                $value = $property->getValue($entity);

                switch ($field->type) {
                    case 'int':
                    case 'integer':
                        $value = (integer)$value;
                        break;

                    case 'float':
                        $value = (float)$value;
                        break;

                    case 'boolean':
                        $value = (integer)in_array($value, ['true', 'yes', 'on']);
                        break;

                    case 'json':
                        $value = json_encode($value);
                        break;

                    case 'datetime':
                        if ($value == '0000-00-00 00:00:00') {
                            $value = '1001-01-01 00:00:00';
                        }
                        break;

                    default:
                        // Leave the value alone
                        break;
                }
            }

            $properties[$colName] = $value;
        }

        return $properties;
    }

    /**
     * Resolve relations
     *
     * @param object $entity
     *
     * @return void
     */
    public function resolve($entity)
    {
        $entityClass = get_class($entity);
        $meta        = $this->getMeta($entityClass);
        $this->resolveRelations($entity, $meta);
    }
}
