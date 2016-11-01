<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Generators\Dao\Eloquent;

use EdStevo\Generators\Dao\Exceptions\ModelNotFoundException;
use EdStevo\Generators\Dao\Exceptions\RepositoryException;
use EdStevo\Generators\Contracts\Dao\CriteriaContract;
use EdStevo\Generators\Contracts\Dao\DaoBaseContract;
use EdStevo\Generators\Contracts\Dao\GeneratorContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

abstract class DaoBase implements DaoBaseContract, CriteriaContract, GeneratorContract
{

    /**
     * @var \Illuminate\Database\Eloquent\Model
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

    public function __construct(Collection $collection)
    {
        $this->criteria         = $collection;
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
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \EdStevo\Generators\Dao\Exceptions\\RepositoryException
     */
    protected function makeModel() : Model
    {
        $model  = resolve($this->model());

        if (!$model instanceof Model)
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
     * @return  \Illuminate\Database\Eloquent\Model;
     */
    public function store(array $data) : Model
    {
        $data       = $this->cleanData($data);

        return $this->model->create($data);
    }

    /**
     * Retrieve an entry of the resource from the DB by its ID
     *
     * @param  int  $id
     *
     * @return \Illuminate\Database\Eloquent\Model;
     */
    public function find($id) : Model
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
     * @return \Illuminate\Database\Eloquent\Model;
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException;
     */
    public function findOrFail($id) : Model
    {
        $this->applyCriteria();
        return $this->model->findOrFail($id);
    }

    /**
     * Retrieve an entry of the resource from the DB where it matches certain criteria
     *
     * @param  array  $data
     *
     * @return \Illuminate\Database\Eloquent\Model;
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
    public function whereIn(array $ids, string $attribute = 'id')
    {
        $this->applyCriteria();
        return $this->model->whereIn($attribute, $ids)->get();
    }

    /**
     * Update the specified resource in the DB.
     *
     * @param array  $data
     * @param int    $id
     * @param string $attribute
     *
     * @return bool
     */
    public function update(array $data, $id, $attribute = "id") : bool
    {
        $data       = $this->cleanData($data);

        return $this->model->where($attribute, '=', $id)->update($data);
    }

    /**
     * Remove an entry for the specified resource from the DB.
     *
     * @param   \Illuminate\Database\Eloquent\Model  $model
     *
     * @return  boolean
     */
    public function destroy($model) : bool
    {
        return $model->delete();
    }

    /**
     * Retrieve all entries of a resource related to this model from the DB
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     *
     * @return  mixed
     */
    public function getRelation($model, string $relation)
    {
        return $model->$relation;
    }

    /**
     * Retrieve all entries of a resource related to this model from the DB with constraints
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     * @param   array                               $constraints
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    public function getRelationWhere($model, string $relation, array $constraints = []) : Collection
    {
        return $model->$relation()->where($constraints)->get();
    }

    /**
     * Put a new resource in storage that is related to another resource
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     * @param   array                               $data
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     */
    public function storeRelation($model, string $relation, array $data) : Model
    {
        $data       = $this->cleanData($data, $model->$relation()->getRelated());

        $foreignKey         = $model->$relation()->getForeignKey();
        $foreignKey         = explode(".", $foreignKey)[1];
        $data[$foreignKey]  = $model->getId();

        $result     = $model->$relation()->create($data);

        $modelName  = $this->getClassName(get_class($result));
        $eventName  = $this->getEventNamespace($modelName, $modelName, "Created");

        event(new $eventName($result));

        return $result;
    }

    /**
     * Update a relation of the model
     *
     * @param \Illuminate\Database\Eloquent\Model   $model
     * @param string                                $relation
     * @param array                                 $data
     * @param null                                  $id
     * @param string                                $attribute
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateRelation($model, string $relation, array $data, $id = null, $attribute = "id") : Model
    {
        $data    = $this->cleanData($data, $model->$relation()->getRelated());

        if ($model->$relation() instanceof HasMany)
        {
            $this->updateRelationHasMany($model, $relation, $data, $id);
        }

        if ($model->$relation() instanceof BelongsToMany)
        {
            $this->updateRelationBelongsToMany($model, $relation, $data, $id, $attribute);
        }

        if ($model->$relation() instanceof MorphTo)
        {
            $this->updateRelationMorphTo($model, $relation, $data, $id);
        }

        return $this->getRelationWhere($model, $relation, [$attribute => $id])->first();
    }

    /**
     * Update the related model via a has many relationship
     *
     * @param        $model
     * @param string $relation
     * @param array  $data
     * @param        $id
     * @param string $attribute
     *
     * @return bool
     */
    private function updateRelationHasMany($model, string $relation, array $data, $id, $attribute = 'id') : bool
    {
        return $model->$relation()->where($attribute, $id)->first()->update($data);
    }

    /**
     * Update the related model via a many to many relationship
     *
     * @param \Illuminate\Database\Eloquent\Model   $model
     * @param string                                $relation
     * @param array                                 $data
     * @param int                                   $id
     *
     * @return mixed
     */
    private function updateRelationBelongsToMany($model, string $relation, array $data, $id, $attribute = 'id') : bool
    {
        return $model->$relation()->where($attribute, $id)->first()->update($data);
    }

    private function updateRelationMorphTo($model, string $relation, array $data, $id)
    {
//        TODO: Finish
    }

    /**
     * Associate a model with a relation via a pivot
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relationship
     * @param   \Illuminate\Database\Eloquent\Model $relation
     *
     * @param   null
     */
    public function attach($model, string $relationship, $relation, array $pivot_data = [])
    {
        $result         = $model->$relationship()->attach($relation->id, $pivot_data);

        $modelName      = $this->getClassName(get_class($model));
        $relationName   = $this->getClassName(get_class($relation));
        $eventName      = $this->getEventNamespace($modelName, $relationName, "Attached");

        event(new $eventName($model, $relation));

        return $result;
    }

    /**
     * Sync a model and its relations via a pivot
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     * @param   int/string                          $relation_id
     * @param   bool                                $detaching
     *
     * @param   array
     */
    public function sync($model, string $relationship, $relation_id, bool $detaching = true)
    {
        return $model->$relationship()->sync($relation_id, $detaching);
    }

    /**
     * Dissociate a model with a relation via a pivot
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relationship
     * @param   \Illuminate\Database\Eloquent\Model $relation
     *
     * @param   bool
     */
    public function detach($model, string $relationship, $relation) : bool
    {
        $result         = $model->$relationship()->detach($relation->id);

        $modelName      = $this->getClassName(get_class($model));
        $relationName   = $this->getClassName(get_class($relation));
        $eventName      = $this->getEventNamespace($modelName, $relationName, "Detached");

        event(new $eventName($model, $relation));

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
    public function getRelationModel($model, $relation) : string
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
    private function getEventNamespace(string $parentModel, string $relationModel, string $event) : string
    {
        return $this->getEventsNamespace() . $parentModel . "\\" . $relationModel . "\\" . $relationModel . $event;
    }

    /**
     * Generate examples of this model
     *
     * @param array $data
     * @param bool  $persist
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function generate(array $data = [], bool $persist = true) : Model
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
     * @return \Illuminate\Database\Eloquent\Model
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
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    private function cleanData(array $data, Model $model = null) : array
    {
        if (is_null($model))
            $model          = $this->model;

        $allowed_fields     = $model->getFillable();
        $data               = collect($data);

        $data               = $data->only($allowed_fields);
        $data               = $data->filter(function($item, $key) {
            if ($item)
                return $item;
        });

        return $data->toArray();
    }
}