<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Generators\Dao\Eloquent;

use EdStevo\Generators\Contracts\Dao\EventsContract;
use EdStevo\Generators\Dao\DaoModel;
use EdStevo\Generators\Dao\Exceptions\ModelNotFoundException;
use EdStevo\Generators\Dao\Exceptions\RepositoryException;
use EdStevo\Generators\Contracts\Dao\CriteriaContract;
use EdStevo\Generators\Contracts\Dao\DaoBaseContract;
use EdStevo\Generators\Contracts\Dao\GeneratorContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

abstract class DaoBase implements CriteriaContract, DaoBaseContract, EventsContract, GeneratorContract
{

    /**
     * @var \EdStevo\Generators\Dao\DaoModel
     */
    protected $model;

    /**
     * @var Collection
     */
    protected $criteria;

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * Elect whether to have events for this dao
     *
     * @var bool
     */
    protected $events   = true;

    /**
     * @var bool
     */
    protected $skipEvents   = false;

    /**
     * Specify relationships which should not broadcast events
     *
     * @var array
     */
    protected $skipEventsForRelationships = [];

    public function __construct(Collection $collection)
    {
        $this->criteria         = $collection;
        $this->resetEvents();
        $this->resetScope();
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract protected function model();


    /**
     * Set the model for the repository
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     *
     * @throws \EdStevo\Generators\Dao\Exceptions\\RepositoryException
     */
    protected function makeModel() : DaoModel
    {
        $model  = resolve($this->model());

        if (!$model instanceof DaoModel)
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
    }

    /**
     * Retrieve all entries of the resource from the DB
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all() : Collection
    {
        $this->applyCriteria();
        return $this->model->get();
    }

    /**
     * Put a new entry for the resource in the DB
     *
     * @param   array  $data
     *
     * @return  \EdStevo\Generators\Dao\DaoModel;
     */
    public function store(array $data) : DaoModel
    {
        $data   = $this->cleanData($data);

        $model  = $this->model->create($data);

        $this->fireModelEvent("Created", $model);

        return $model;
    }

    /**
     * Find a current instance or create a new one
     *
     * @param array $data
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function firstOrCreate(array $data) : DaoModel
    {
        $data       = $this->cleanData($data);

        return $this->model->firstOrCreate($data);
    }

    /**
     * Retrieve an entry of the resource from the DB by its ID
     *
     * @param  int  $id
     *
     * @return \EdStevo\Generators\Dao\DaoModel|null;
     */
    public function find($id)
    {
        $this->applyCriteria();
        return $this->model->find($id);
    }

    /**
     * Retrieve an entry of the resource from the DB
     * If the resource cannot be found, throw ModelNotFoundException
     *
     * @param  int  $id
     *
     * @return \EdStevo\Generators\Dao\DaoModel;
     *
     * @throws \EdStevo\Generators\Dao\DaoModelNotFoundException;
     */
    public function findOrFail($id) : DaoModel
    {
        $this->applyCriteria();
        return $this->model->findOrFail($id);
    }

    /**
     * Retrieve an entry of the resource from the DB where it matches certain criteria
     *
     * @param  array  $data
     *
     * @return \EdStevo\Generators\Dao\DaoModel;
     */
    public function findWhere(array $data)
    {
        return $this->model->where($data)->first();
    }

    /**
     * Retrieve multiple entries of the resource from the DB where it matches certain criteria
     *
     * @param  array  $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function where(array $data) : Collection
    {
        $this->applyCriteria();
        return $this->model->where($data)->get();
    }

    /**
     * Retrieve multiple entries of the resource from the DB where it matches an attribute
     *
     * @param  array  $ids
     * @param  string  $attribute
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function whereIn(array $ids, string $attribute = null)
    {
        $attribute  = ($attribute) ?: $this->model->getIdField();

        $this->applyCriteria();
        return $this->model->whereIn($attribute, $ids)->get();
    }

    /**
     * Update the specified resource in the DB.
     *
     * @param \EdStevo\Generators\Dao\DaoModel  $model
     * @param array     $data
     * @param string $attribute
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function update(DaoModel $model, array $data) : DaoModel
    {
        $data       = $this->cleanData($data);

        foreach ($data as $key => $value)
        {
            $model->$key    = $value;
        }

        $model->save();

        $model->update($data);

        $this->fireModelEvent("Updated", $model);

        return $model;
    }

    /**
     * Remove an entry for the specified resource from the DB.
     *
     * @param   \EdStevo\Generators\Dao\DaoModel  $model
     *
     * @return  boolean
     */
    public function destroy(DaoModel $model) : bool
    {
        $result = $model->delete();

        if ($result)
        {
            $this->fireModelEvent("Destroyed", $model);
        }

        return $result;
    }

    /**
     * Retrieve all entries of a resource related to this model from the DB
     *
     * @param   \EdStevo\Generators\Dao\DaoModel $model
     * @param   string                              $relation
     *
     * @return  mixed
     */
    public function getRelation(DaoModel $model, string $relation)
    {
        return $model->$relation;
    }

    /**
     * Retrieve all entries of a resource related to this model from the DB with constraints
     *
     * @param   \EdStevo\Generators\Dao\DaoModel $model
     * @param   string                              $relation
     * @param   array                               $constraints
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    public function getRelationWhere(DaoModel $model, string $relation, array $constraints = []) : Collection
    {
        return $model->$relation()->where($constraints)->get();
    }


    /**
     * Update a relation of the model
     *
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param string                           $relationship
     * @param \EdStevo\Generators\Dao\DaoModel $relation
     * @param array                            $data
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function updateRelation(DaoModel $model, string $relationship, DaoModel $relation, array $data) : DaoModel
    {
        $data       = $this->cleanData($data, $relation);

        if ($model->$relationship() instanceof HasMany)
        {
            $this->updateRelationHasMany($model, $relationship, $relation, $data);
        }

        if ($model->$relationship() instanceof BelongsToMany)
        {
            $this->updateRelationBelongsToMany($model, $relationship, $relation, $data);
        }

        if ($model->$relationship() instanceof MorphMany)
        {
            $this->updateRelationMorphMany($model, $relationship, $relation, $data);
        }

        $updatedRelation    = $this->getRelationWhere($model, $relationship, [$relation->getIdField() => $relation->getId()])->first();

        if (!in_array($relationship, $this->skipEventsForRelationships))
        {
            $this->fireModelEvent("Updated", $model, $updatedRelation);
        }

        return $updatedRelation;
    }

    /**
     * Put a new resource in storage that is related to another resource
     *
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param string                           $relationship
     * @param array                            $data
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function storeRelation(DaoModel $model, string $relationship, array $data = []) : DaoModel
    {
        $data       = $this->cleanData($data, $model->$relationship()->getRelated());

        $foreignKey         = $model->$relationship()->getQualifiedForeignKeyName();
        $foreignKey         = explode(".", $foreignKey)[1];
        $data[$foreignKey]  = $model->getId();

        $result     = $model->$relationship()->create($data);

        if (!in_array($relationship, $this->skipEventsForRelationships))
        {
            $this->fireModelEvent("Created", $model, $result);
        }

        return $result;
    }

    /**
     * Update the related model via a has many relationship
     *
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param string                           $relationship
     * @param \EdStevo\Generators\Dao\DaoModel $relation
     * @param array                            $data
     *
     * @return bool
     */
    private function updateRelationHasMany(DaoModel $model, string $relationship, DaoModel $relation, array $data) : bool
    {
        return $model->$relationship()->where($relation->getIdField(), $relation->getId())->first()->update($data);
    }

    /**
     * Update the related model via a belongs to many relationship
     *
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param string                           $relationship
     * @param \EdStevo\Generators\Dao\DaoModel $relation
     * @param array                            $data
     *
     * @return bool
     */
    private function updateRelationBelongsToMany(DaoModel $model, string $relationship, DaoModel $relation, array $data) : bool
    {
        return $model->$relationship()->where($relation->getIdField(), $relation->getId())->first()->update($data);
    }

    /**
     * Update the related model via a polymorphic relationship
     *
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param string                           $relationship
     * @param \EdStevo\Generators\Dao\DaoModel $relation
     * @param array                            $data
     *
     * @return bool
     */
    private function updateRelationMorphMany(DaoModel $model, string $relationship, DaoModel $relation, array $data) : bool
    {
        return $model->$relationship()->where($relation->getIdField(), $relation->getId())->first()->update($data);
    }

    /**
     * Destroy a relation and fire an event attached with this model
     *
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param string                           $relationship
     * @param \EdStevo\Generators\Dao\DaoModel $relation
     *
     * @return bool|null
     */
    public function destroyRelation(DaoModel $model, string $relationship, DaoModel $relation)
    {
        $result         = $relation->delete();

        if (!in_array($relationship, $this->skipEventsForRelationships))
        {
            $this->fireModelEvent("Destroyed", $model, $relation);
        }

        return $result;
    }

    /**
     * Associate a model with a relation via a pivot
     *
     * @param   \EdStevo\Generators\Dao\DaoModel    $model
     * @param   string                              $relationship
     * @param   \EdStevo\Generators\Dao\DaoModel    $relation
     * @param   array                               $pivot_data
     *
     * @param   null
     */
    public function attach(DaoModel $model, string $relationship, DaoModel $relation, array $pivot_data = [])
    {
        $result         = $model->$relationship()->attach($relation->getId(), $pivot_data);

        if (!in_array($relationship, $this->skipEventsForRelationships))
        {
            $this->fireModelEvent("Attached", $model, $relation);
        }

        return $result;
    }

    /**
     * Sync a model and its relations via a pivot
     *
     * @param   \EdStevo\Generators\Dao\DaoModel $model
     * @param   string                              $relation
     * @param   int/string                          $relation_id
     * @param   bool                                $detaching
     *
     * @param   array
     */
    public function sync(DaoModel $model, string $relationship, $relation_id, bool $detaching = true)
    {
        return $model->$relationship()->sync($relation_id, $detaching);
    }

    /**
     * Update a pivot table across a many to many relationship
     *
     * @param        $model
     * @param string $relationship
     * @param        $relation
     * @param array  $pivot_data
     *
     * @return mixed
     */
    public function updatePivot(DaoModel $model, string $relationship, DaoModel $relation, array $pivot_data = [])
    {
        $result         = $model->$relationship()->updateExistingPivot($relation->getId(), $pivot_data);

        if (!in_array($relationship, $this->skipEventsForRelationships))
        {
            $this->fireModelEvent("Updated", $model, $relation);
        }

        return $result;
    }

    /**
     * Dissociate a model with a relation via a pivot
     *
     * @param   \EdStevo\Generators\Dao\DaoModel $model
     * @param   string                              $relationship
     * @param   \EdStevo\Generators\Dao\DaoModel $relation
     *
     * @param   bool
     */
    public function detach(DaoModel $model, string $relationship, DaoModel $relation) : bool
    {
        $result         = $model->$relationship()->detach($relation->getId());

        if (!in_array($relationship, $this->skipEventsForRelationships))
        {
            $this->fireModelEvent("Detached", $model, $relation);
        }

        return $result;
    }

    /**
     * Get the validation rules associated with a model
     *
     * @return  array
     */
    public function getRules() : array
    {
        return $this->model->rules();
    }

    /**
     * Throw exception when model cannot be found
     *
     * @throws  \EdStevo\Generators\Dao\Exceptions\ModelNotFoundException
     */
    public function notFound()
    {
        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }

    /**
     * Reset the repository skip events status
     *
     * @return $this
     */
    public function resetEvents()
    {
        $this->skipEvents(false);
        return $this;
    }

    /**
     * Set the repository to skip the events
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skipEvents(bool $status = true)
    {
        $this->skipEvents   = $status;
        return $this;
    }

    /**
     * @return $this
     */
    public function resetScope()
    {
        $this->skipCriteria(false);
        return $this;
    }

    /**
     * @param   bool    $status
     * @return  $this
     */
    public function skipCriteria(bool $status = true)
    {
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * @return  mixed
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param   CriteriaBase    $criteria
     * @return  $this
     */
    public function getByCriteria(CriteriaBase $criteria)
    {
        $this->model    = $criteria->apply($this->model, $this);
        return $this;
    }

    /**
     * @param   CriteriaBase    $criteria
     * @return  $this
     */
    public function pushCriteria(CriteriaBase $criteria)
    {
        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * @return $this
     */
    public function  applyCriteria()
    {
        if($this->skipCriteria === true)
            return $this;

        foreach($this->getCriteria() as $criteria) {
            if($criteria instanceof CriteriaBase)
                $this->model = $criteria->apply($this->model, $this);
        }

        return $this;
    }

    /**
     * Get the Name of the Related Model
     *
     * @param $model
     * @param $relation
     *
     * @return string
     */
    public function getRelationModel(DaoModel $model, DaoModel $relation) : string
    {
        $relatedModel   = $model->$relation()->getRelated();
        $className      = get_class($relatedModel);
        return $this->getClassName($className);
    }

    /**
     * Get the Class Name from a namespace
     *
     * @param $namespace
     *
     * @return string
     */
    public function getClassName($namespace) : string
    {
        $namespaceParts = explode("\\", $namespace);
        return collect($namespaceParts)->last();
    }

    /**
     * Get the app namespace
     *
     * @return string
     */
    private function getAppNamespace() : string
    {
        return app()->getNamespace();
    }

    /**
     * Get the events namespace
     *
     * @return string
     */
    private function getEventsNamespace() : string
    {
        return $this->getAppNamespace() . "Events\\";
    }

    /**
     * Get the namespace for an event
     *
     * @param string $parentModel
     * @param string $relationModel
     * @param string $event
     *
     * @return string
     */
    private function getEventNamespace(string $eventType, string $model, string $relation = null) : string
    {
        $eventString    = $this->getEventsNamespace() . $model;

        if ($relation)
        {
            $eventString    = $eventString . "\\" . $relation . "\\" . $relation;
        }

        if (!$relation)
        {
            $eventString    = $eventString . "\\" . $model . "\\" . $model;
        }

        $eventString    = $eventString . $eventType;

        return $eventString;
    }

    /**
     * Generate examples of this model
     *
     * @param array $data
     * @param bool  $persist
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function generate(array $data = [], bool $persist = true) : DaoModel
    {
        if ($persist)
        {
            return factory($this->model())->create($data);
        }

        return factory($this->model())->make($data);
    }

    /**
     * Generate multiple examples of this model
     *
     * @param int   $number
     * @param array $data
     * @param bool  $persist
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function generateMultiple(int $number = 2, array $data = [], bool $persist = true) : Collection
    {
        if ($persist)
        {
            return factory($this->model(), $number)->create($data);
        }

        return factory($this->model(), $number)->make($data);
    }

    /**
     * Clean a data set to only allow the correct data for this model
     *
     * @param array                               $data
     * @param \EdStevo\Generators\Dao\DaoModel $model
     *
     * @return array
     */
    private function cleanData(array $data, DaoModel $model = null) : array
    {
        if (is_null($model))
            $model          = $this->model;

        $allowed_fields     = $model->getFillable();
        $data               = collect($data);

        $data               = $data->only($allowed_fields);

        return $data->toArray();
    }

    /**
     * Fire a model event
     *
     * @param string                                   $eventType
     * @param \EdStevo\Generators\Dao\DaoModel      $model
     * @param \EdStevo\Generators\Dao\DaoModel|NULL $relation
     *
     * @return void;
     */
    private function fireModelEvent(string $eventType, DaoModel $model, DaoModel $relation = null)
    {
        if (!$this->events || $this->skipEvents)
            return;

        if ($relation)
        {
            $this->fireRelationalEvent($model, $relation, $eventType);
        } else {
            $this->fireSelfEvent($model, $eventType);
        }
    }

    /**
     * Fire a model event
     *
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param string                              $eventType
     */
    private function fireSelfEvent(DaoModel $model, string $eventType)
    {
        $modelName  = $this->getClassName(get_class($this->model));
        $eventName  = $this->getEventNamespace($eventType, $modelName);

        if (!class_exists($eventName))
        {
            throw new RepositoryException("Event {$eventName} Does Not Exist");
        }

        event(new $eventName($model));
    }

    /**
     * Fire a relational model event
     *
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param \EdStevo\Generators\Dao\DaoModel $relation
     * @param string                              $eventType
     */
    private function fireRelationalEvent(DaoModel $model, DaoModel $relation, string $eventType)
    {
        $modelName      = $this->getClassName(get_class($this->model));
        $relationName   = $this->getClassName(get_class($relation));
        $eventName      = $this->getEventNamespace($eventType, $modelName, $relationName);

        if (!class_exists($eventName))
        {
            throw new RepositoryException("Event {$eventName} Does Not Exist");
        }

        broadcast(new $eventName($model, $relation))->toOthers();
    }

    /**
     * Inherit function
     * Clean up class
     */
    public function __destruct()
    {
        $this->skipEvents(false);
    }
}