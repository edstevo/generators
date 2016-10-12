<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace App\Dao\Eloquent;

use App\Contracts\Dao\CriteriaContract;
use App\Contracts\Dao\DaoBase as DaoBaseContract;
use App\Dao\CriteriaBase;
use App\Dao\Exceptions\ModelNotFoundException;
use App\Dao\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class DaoBase implements DaoBaseContract, CriteriaContract
{

    /**
     * @var
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
     * @return Model
     * @throws RepositoryException
     */
    protected function makeModel() {
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
    public function all()
    {
        $this->applyCriteria();
        return $this->model->all();
    }

    /**
     * Put a new entry for the resource in the DB
     *
     * @param   array  $data
     *
     * @return  \Illuminate\Database\Eloquent\Model;
     */
    public function store(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Retrieve an entry of the resource from the DB by its ID
     *
     * @param  int  $id
     *
     * @return \Illuminate\Database\Eloquent\Model;
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
     * @return \Illuminate\Database\Eloquent\Model;
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException;
     */
    public function findOrFail($id)
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
    public function where(array $data)
    {
        $this->applyCriteria();
        return $this->model->where($data)->get();
    }

    /**
     * Update the specified resource in the DB.
     *
     * @param   array   $data
     * @param   int     $id
     * @param   string  $attribute
     *
     * @return \Illuminate\Database\Eloquent\Model;
     */
    public function update(array $data, $id, $attribute = "id")
    {
        return $this->model->where($attribute, '=', $id)->update($data);
    }

    /**
     * Remove an entry for the specified resource from the DB.
     *
     * @param   \Illuminate\Database\Eloquent\Model  $model
     *
     * @return  boolean
     */
    public function destroy($model)
    {
        return $model->delete();
    }

    /**
     * Retrieve all entries of a resource related to this model from the DB
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    public function getRelation($model, $relation)
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
    public function getRelationWhere($model, $relation, array $constraints = [])
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
    public function storeRelation($model, $relation, array $data)
    {
        return $model->$relation()->create($data);
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
     * @return mixed|void
     */
    public function updateRelation($model, $relation, array $data, $id = null, $attribute = "id")
    {
        if ($model->$relation() instanceof HasMany)
        {
            return $this->updateRelationHasMany($model, $relation, $data, $id);
        }

        if ($model->$relation() instanceof BelongsToMany)
        {
            return $this->updateRelationBelongsToMany($model, $relation, $data, $id);
        }

        if ($model->$relation() instanceof MorphTo)
        {
            return $this->updateRelationMorphTo($model, $relation, $data, $id);
        }
    }

    private function updateRelationHasMany($model, $relation, array $data, $id)
    {
//        TODO: Finish
    }

    /**
     * Update the pivot table on a many to many relationship
     *
     * @param \Illuminate\Database\Eloquent\Model   $model
     * @param string                                $relation
     * @param array                                 $data
     * @param int                                   $id
     *
     * @return mixed
     */
    private function updateRelationBelongsToMany($model, $relation, array $data, $id)
    {
        return $model->$relation()->sync($data, false);
    }

    private function updateRelationMorphTo($model, $relation, array $data, $id)
    {
//        TODO: Finish
    }

    /**
     * Associate a model with a relation via a pivot
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     * @param   int                                 $relation_id
     *
     * @param   array
     */
    public function attach($model, $relationship, $relation_id)
    {
        return $model->$relationship()->attach($relation_id);
    }

    /**
     * Sync a model and its relations via a pivot
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     * @param   int/string                          $relation_id
     *
     * @param   array
     */
    public function sync($model, $relationship, $relation_id, $detaching = true)
    {
        return $model->$relationship()->sync($relation_id, $detaching);
    }

    /**
     * Dissociate a model with a relation via a pivot
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     * @param   int/string                          $relation_id
     *
     * @param   array
     */
    public function detach($model, $relationship, $relation_id)
    {
        return $model->$relationship()->detach($relation_id);
    }

    /**
     * Get the validation rules associated with a model
     *
     * @return  array
     */
    public function getRules()
    {
        return $this->model->rules();
    }

    /**
     * Throw exception when model cannot be found
     *
     * @throws  ModelNotFoundException
     */
    public function notFound()
    {
        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }

    /**
     * @return $this
     */
    public function resetScope() {
        $this->skipCriteria(false);
        return $this;
    }

    /**
     * @param   bool    $status
     * @return  $this
     */
    public function skipCriteria($status = true){
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * @return  mixed
     */
    public function getCriteria() {
        return $this->criteria;
    }

    /**
     * @param   CriteriaBase    $criteria
     * @return  $this
     */
    public function getByCriteria(CriteriaBase $criteria) {
        $this->model    = $criteria->apply($this->model, $this);
        return $this;
    }

    /**
     * @param   CriteriaBase    $criteria
     * @return  $this
     */
    public function pushCriteria(CriteriaBase $criteria) {
        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * @return $this
     */
    public function  applyCriteria() {
        if($this->skipCriteria === true)
            return $this;

        foreach($this->getCriteria() as $criteria) {
            if($criteria instanceof CriteriaBase)
                $this->model = $criteria->apply($this->model, $this);
        }

        return $this;
    }

}